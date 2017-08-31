FROM ubuntu:trusty
ARG mysql_root_password

#install java
RUN apt-key adv --keyserver keyserver.ubuntu.com --recv-keys DA1A4A13543B466853BAF164EB9B1D8886F44E2A \
    && touch /etc/apt/sources.list.d/openjdk.list \
    && echo "deb http://ppa.launchpad.net/openjdk-r/ppa/ubuntu trusty main " >>/etc/apt/sources.list.d/openjdk.list \
    && echo "deb-src http://ppa.launchpad.net/openjdk-r/ppa/ubuntu trusty main" >>/etc/apt/sources.list.d/openjdk.list \
    && apt-get update \
    && apt-get -y install openjdk-8-jdk \
    && export JAVA_HOME=/usr/lib/jvm/java-8-openjdk-amd64
ENV JAVA_HOME /usr/lib/jvm/java-8-openjdk-amd64

#install mysql
RUN apt-get update && apt-get install -y software-properties-common language-pack-en-base \
    && echo "mysql-server mysql-server/root_password password $mysql_root_password" | debconf-set-selections \
    && echo "mysql-server mysql-server/root_password_again password $mysql_root_password" | debconf-set-selections \
    && apt-get install -q -y -f \
        mysql-server mysql-client 

#install tools
RUN apt-get install -q -y -f \
        bzip2 zip nano unzip wget

#install teamcity server
RUN mkdir /root/.BuildServer/lib/jdbc/ -p \
    && cd /opt/ \
    && wget https://download.jetbrains.com/teamcity/TeamCity-2017.1.2.tar.gz \
    && tar -xf TeamCity-2017.1.2.tar.gz \
    && wget https://dev.mysql.com/get/Downloads/Connector-J/mysql-connector-java-5.1.43.tar.gz \
    && tar -xf mysql-connector-java-5.1.43.tar.gz \
    && mv mysql-connector-java-5.1.43/mysql-connector-java-5.1.43-bin.jar /root/.BuildServer/lib/jdbc/

CMD service mysql start \
    && /opt/TeamCity/bin/teamcity-server.sh start \
    && touch /opt/TeamCity/logs/teamcity-server.log \
    && tail -f /opt/TeamCity/logs/teamcity-server.log