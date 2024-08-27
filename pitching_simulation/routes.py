from flask_restful import Api

from pitching_simulation.resources import PitchResource
from pitching_simulation.resources import SessionResource

def register_routes(api: Api):
    api.add_resource(PitchResource, '/pitch')
    api.add_resource(SessionResource, '/sessions')