from flask import request, jsonify
from flask_restful import Resource
from pitching_simulation import conversational_rag_chain
from pitching_simulation.sessions import storage

class PitchResource(Resource):
    def post(self):
        data = request.get_json()
        session_id = data.get('session_id')
        input_msg =  data.get('input')

        mission_status = 'ongoing'

        def end_conversation(status):
            nonlocal mission_status
            storage.pop(session_id)
            mission_status = status

        if input_msg.lower() == 'keluar':
            end_conversation('quit')
            return jsonify({"message": "Bye, let's have a chat again another time!", "mission_status": mission_status})

        response = conversational_rag_chain.invoke(
            {"input": input_msg},
            config={
                "configurable": {"session_id": session_id}
            }
        )

        response_msg = response["answer"]

        if "deal" in response_msg.lower():
            end_conversation('success')

        if "bye" in response_msg.lower():
            end_conversation('failed')
        
        return jsonify({"message": response_msg, "mission_status": mission_status})
    
class SessionResource(Resource):
    def get(self):
        sessions = list(storage.keys())
        return jsonify({"message": sessions})