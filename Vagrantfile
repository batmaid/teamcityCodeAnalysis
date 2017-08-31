VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|

  config.vm.box = "ubuntu/trusty64"
  config.vm.network "private_network", ip: "192.168.50.8"
  config.vm.provider "virtualbox" do |v|
    v.memory = 5000
    v.cpus = 2
    v.name = "teamcity box"
  end

end
