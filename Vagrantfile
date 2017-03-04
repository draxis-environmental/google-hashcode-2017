VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(2) do |config|
  
  config.vm.box = "ubuntu/trusty64"
  config.vm.network "private_network", ip: "192.168.100.200"
  config.vm.hostname = "hashcode"

  config.vm.provider "virtualbox" do |v|
    v.memory = 5120
    v.cpus = 2
  end

  config.vm.provision :docker
  config.vm.provision :docker_compose, yml: "/vagrant/docker-compose.yml", rebuild: true, run: "always"

end