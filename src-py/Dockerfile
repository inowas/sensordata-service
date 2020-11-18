FROM python:3.8-buster

MAINTAINER Ralf Junghanns <ralf.junghanns@gmail.com>

# Install the requirements
COPY ./app /app
WORKDIR /app
RUN pip install -r ./requirements.txt
EXPOSE 5000
RUN chmod +x ./entrypoint.sh
ENTRYPOINT ["sh", "entrypoint.sh"]
