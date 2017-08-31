FROM ubuntu:trusty
ARG mysql_root_password
ARG server_dns_name
ARG agent_name

#install java for teamcity agent
RUN apt-key adv --keyserver keyserver.ubuntu.com --recv-keys DA1A4A13543B466853BAF164EB9B1D8886F44E2A \
    && touch /etc/apt/sources.list.d/openjdk.list \
    && echo "deb http://ppa.launchpad.net/openjdk-r/ppa/ubuntu trusty main " >>/etc/apt/sources.list.d/openjdk.list \
    && echo "deb-src http://ppa.launchpad.net/openjdk-r/ppa/ubuntu trusty main" >>/etc/apt/sources.list.d/openjdk.list \
    && apt-get update \
    && apt-get -y install openjdk-8-jdk \
    && export JAVA_HOME=/usr/lib/jvm/java-8-openjdk-amd64
ENV JAVA_HOME /usr/lib/jvm/java-8-openjdk-amd64

#install batmaid dependencies foo
RUN apt-get update && apt-get install -y software-properties-common language-pack-en-base \
    && LC_ALL=en_US.UTF-8 add-apt-repository ppa:ondrej/php \
    && apt-get update \
    && echo "mysql-server mysql-server/root_password password $mysql_root_password" | debconf-set-selections \
    && echo "mysql-server mysql-server/root_password_again password $mysql_root_password" | debconf-set-selections \
    && apt-get install -q -y -f \
         mysql-server mysql-client \
         php7.1-cli php7.1-mysql php7.1-curl php7.1-intl php7.1-apcu php7.1-imagick php7.1-mcrypt \
         php7.1-gmp php7.1-xml php7.1-mbstring php7.1-zip php7.1-gd \
         bzip2 zip nano git

COPY php/mods-available/batmaid.ini /etc/php/7.1/mods-available/
RUN cd /etc/php/7.1/cli/conf.d/ && ln -s ../../mods-available/batmaid.ini 99-batmaid.ini
COPY auth.json /root/.composer/

#install teamcity agent
RUN mkdir /opt/agent -p \
    && apt-get install -y unzip wget \
    && cd /opt/agent \
    && wget https://teamcity.elpster.com/update/buildAgent.zip \
    && unzip -o buildAgent.zip \
    && mkdir /opt/agent/logs -p \
    && touch /opt/agent/logs/teamcity-agent.log \
    && mv conf/buildAgent.dist.properties conf/buildAgent.properties \
    && sed -i "s/serverUrl\=.*/serverUrl=http:\/\/$server_dns_name:8111\//g" conf/buildAgent.properties \
    && sed -i "s/name\=/name=$agent_name/g" conf/buildAgent.properties \
    && cat conf/buildAgent.properties \
    && chmod +x /opt/agent/bin/*.sh

#install PhpStorm for code analysis
RUN cd /opt \
    && wget https://download.jetbrains.com/webide/PhpStorm-2017.2.tar.gz \
    && tar -xf PhpStorm-2017.2.tar.gz

#CMD tail -f /dev/null #hack to keep container open, should be
CMD service mysql start \
    && /opt/agent/bin/agent.sh start \
    && tail -f /opt/agent/logs/teamcity-agent.log