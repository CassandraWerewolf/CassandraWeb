# Cassandra Web

To run:

1. Request a recent database dump from maintainer
2. Place werewolf_data.sql in the sql folder
3. Run `docker compose up -d --build`

The first start loads the dump into a new database, which takes a few minutes.
To reload the data later, run `docker compose down -v` and start again.

You can access the application at http://localhost:5000

On macOS, port 5000 is used by AirPlay Receiver. Turn it off in
System Settings → General → AirDrop & Handoff, or change the port in docker-compose.yml.
