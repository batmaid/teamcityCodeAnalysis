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
8. use the snippets in `/teamcity` to setup command line build steps, for code analysis + automated tests

#how it works

The build step running `inspect_snipped.sh`, invokes phpStorm.
It takes these inputs:
* Path to project root directory on the agent. (this can be set to a `%checkout dir%` teamcity variable).
* Also the path to the inspection profile configuration file. This file is inside the .idea folder, it can be added in git.
  - I recommend using the Project_Default.xml, that way, you have the same results on the server as in the IDE.
* A temporary output directory where to place files with the results.
* A subdirectory in the project on which to run the inspections.

If that works well and phpStorm generates the outputs, they need to be transformed and combined into a single file.
This is done by `TeamcityCodeAnalysisTransformCommand`.
After the output file is generated, an instruction is printed in the command line. Teamcity reads the instruction and starts parsing the output file.

