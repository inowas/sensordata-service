# SensorData service

## Sync UIT with server

```
apt install sshpass
```

```
0 * * * *  cd /srv/docker/sensordata-service && ./src-php/loadFiles.sh SERVER_PASSWORD
```

