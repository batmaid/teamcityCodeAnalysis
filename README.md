# teamcityCodeAnalysis
Docker config + tools necessary to run PhpStorm code analysis on Teamcity


#installation

1. install docker + docker-compose.
Consider changing the default passwords in the `docker-compose.yml`
2. go to `teamcity` directory, run `docker-compose build`
3. after containers have built, start them detached, using `docker-compose up -d` 
4. teamcity needs a database, create one using 
`docker exec -ti batserver mysql -pserver_password -e "create database teamcity"`
5. infrastructure is setup now, finish teamcity setup by browsing to port 8111
6. When asked for database connection info, select mysql, click to load drivers. database = teamcity, user = root, password = server_password
6. create your teamcity account, go to "Agents" authorize the `batagent1`
7. copy your license files to the teamcity agent, they are usually located in your home directory in `~/.PhpStorm2017.2` use the command `docker cp files batagent1:/root/`
