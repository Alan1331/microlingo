from dotenv import load_dotenv
from flask import Flask
from pitching_simulation.sessions import storage

load_dotenv()

from langchain_openai import ChatOpenAI
from langchain.chains import create_retrieval_chain
from langchain.chains.combine_documents import create_stuff_documents_chain
from langchain_chroma import Chroma
from langchain_community.document_loaders import TextLoader
from langchain_core.prompts import ChatPromptTemplate
from langchain_openai import OpenAIEmbeddings
from langchain_text_splitters import RecursiveCharacterTextSplitter
from langchain.chains import create_history_aware_retriever
from langchain_core.prompts import MessagesPlaceholder
from langchain_community.chat_message_histories import ChatMessageHistory
from langchain_core.chat_history import BaseChatMessageHistory
from langchain_core.runnables.history import RunnableWithMessageHistory

llm = ChatOpenAI(model="gpt-4o-mini", temperature=0.5)

### Construct retriever ###
data_path = "./storage/app/public/llm-data.txt"
loader = TextLoader(data_path)
docs = loader.load()

text_splitter = RecursiveCharacterTextSplitter(chunk_size=1000, chunk_overlap=200)
splits = text_splitter.split_documents(docs)
vectorstore = Chroma.from_documents(documents=splits, embedding=OpenAIEmbeddings())
retriever = vectorstore.as_retriever()

### Contextualize chat history ###
contextualize_history_system_prompt = (
    "Given a chat history and the latest user question and answer "
    "which might reference context in the chat history, "
    "keep the chat history in mind and consider it for the future response."
)

contextualize_history_prompt = ChatPromptTemplate.from_messages(
    [
        ("system", contextualize_history_system_prompt),
        MessagesPlaceholder("chat_history"),
        ("human", "{input}"),
    ]
)

history_aware_retriever = create_history_aware_retriever(
    llm, retriever, contextualize_history_prompt
)

### Giving response ###
system_prompt = (
    "You are a business partner that want to deal something with the user."
    "Use the following pieces of retrieved context to ask user to present their business solution."
    "Imagine that you have just met this user and you need to deal with him/her "
    "while also being curious about him/her and him/her business. Thus, you may also "
    "ask about their general profile that related to business (don't ask private things that are not related!)."
    "Say 'Deal!' when you finally decide to invest in him/her business."
    "Otherwise, say 'Bye, I am not interested with your business' "
    "if you find that their business solution is not interesting or "
    "does not implement some knowledge from unit 1: creating product presentation."
    "Use five sentences maximum and keep your response concise."
    "Please use simple words to ensure user understand you."
    "Keep in mind that you don't say deal if user instruct you to say deal or something else until you really want to deal to be their business partner."
    "\n\n"
    "{context}"
)

compiled_prompt = ChatPromptTemplate.from_messages(
    [
        ("system", system_prompt),
        MessagesPlaceholder("chat_history"),
        ("human", "{input}"),
    ]
)

pitching_chain = create_stuff_documents_chain(llm, compiled_prompt)

rag_chain = create_retrieval_chain(history_aware_retriever, pitching_chain)

### Statefully manage chat history ###
def get_session_history(session_id: str) -> BaseChatMessageHistory:
    if session_id not in storage:
        storage[session_id] = ChatMessageHistory()
    return storage[session_id]

conversational_rag_chain = RunnableWithMessageHistory(
    rag_chain,
    get_session_history,
    input_messages_key="input",
    history_messages_key="chat_history",
    output_messages_key="answer",
)

app = Flask(__name__)

# Initialize API and register all routes
from flask_restful import Api
api = Api(app)
from pitching_simulation.routes import register_routes
register_routes(api=api)