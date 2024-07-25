from flask import Flask, request, jsonify
import time
import google.generativeai as genai
from google.generativeai.types import HarmCategory, HarmBlockThreshold
import os
from dotenv import load_dotenv

app = Flask(__name__)

# Load environment variables
load_dotenv()

@app.route('/analyze', methods=['POST'])
def process_video():
    genai.configure(api_key=os.getenv('GOOGLE_API_KEY'))
    data = request.get_json()
    video_names = data.get('video_names')

    message = ''

    for video_name in video_names:
        # Upload the video and print a confirmation.
        video_path = f"public/storage/videos/{video_name}"

        print(f"Uploading file...")
        video_file = genai.upload_file(path=video_path)
        print(f"Completed upload: {video_file.uri}")

        # Check whether the file is ready to be used.
        while video_file.state.name == "PROCESSING":
            print('.', end='')
            time.sleep(10)
            video_file = genai.get_file(video_file.name)

        if video_file.state.name == "FAILED":
            raise ValueError(video_file.state.name)

        # Create the prompt.
        prompt_is_save = False
        while(not prompt_is_save):
            prompt = "Analyze the speaker's explanation from this video and turn all important explanation into text"

            # Choose a Gemini model.
            model = genai.GenerativeModel(model_name="gemini-1.5-pro")

            try:
                # Make the LLM request.
                print("Making LLM inference request...")
                response = model.generate_content([video_file, prompt], request_options={"timeout": 600})
                message += response.text
                prompt_is_save = True
            except ValueError:
                prompt_is_save = False
                print("Prompt was not safety, paraphrasing the prompt!")
                prompt = paraphrase_prompt(prompt)

    return jsonify({"message": message})

def paraphrase_prompt(original_prompt):
    model = genai.GenerativeModel(model_name='gemini-1.5-flash')
    paraphrase_prompt = "Please paraphrase the following prompt to enhance safety: \"" + original_prompt + "\""
    try:
        response = model.generate_content(
            [paraphrase_prompt],
            request_options={"timeout": 600},
            safety_settings={
                HarmCategory.HARM_CATEGORY_HATE_SPEECH: HarmBlockThreshold.BLOCK_NONE,
                HarmCategory.HARM_CATEGORY_HARASSMENT: HarmBlockThreshold.BLOCK_NONE,
                HarmCategory.HARM_CATEGORY_DANGEROUS_CONTENT: HarmBlockThreshold.BLOCK_NONE,
                HarmCategory.HARM_CATEGORY_SEXUALLY_EXPLICIT: HarmBlockThreshold.BLOCK_NONE,
                HarmCategory.HARM_CATEGORY_UNSPECIFIED: HarmBlockThreshold.BLOCK_NONE,
            }
        )
        result = response.text
        return result
    except Exception:
        return original_prompt

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5000)
