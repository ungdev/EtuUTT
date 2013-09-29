
from locust import Locust, TaskSet, task

class WebsiteTasks(TaskSet):
    def on_start(self):
        self.client.post("/user/external", {
            "login": "test",
            "password": "Er45Df12#"
        })

    @task
    def index(self):
        self.client.get("/")

    @task
    def events(self):
        self.client.get("/events")

    @task
    def forum(self):
        self.client.get("/forum")

    @task
    def orgas(self):
        self.client.get("/orgas")

    @task
    def pageTeam(self):
        self.client.get("/page/l-equipe")

    @task
    def pageLegalities(self):
        self.client.get("/page/mentions-legales")

    @task
    def uvs(self):
        self.client.get("/uvs")

    @task
    def trombi(self):
        self.client.get("/trombi")

    @task
    def wikiUNG(self):
        self.client.get("/wiki/ung")

class WebsiteUser(Locust):
    task_set = WebsiteTasks
    min_wait = 5000
    max_wait = 15000