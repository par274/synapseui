## A web-based and modular with graph/node-powered AI interface

```
 .oooooo..o                                                                ooooo      ooo ooooo 
d8P'    `Y8                                                                `888'      `8' `888' 
Y88bo.      oooo   ooo  ooo. .oo.    .oooo.   oo.ooooo.  .oooo.o  .ooooo.   888        8   888  
`"Y8888o.   `88.  .8'  `888P"Y88b  `P  )88b   888' `88b d88(  "8 d88' `88b  888        8   888  
    `"Y88b   `88..8'    888   888   .oP"888   888   888 `"Y88b.  888ooo888  888        8   888  
oo    .d8P    `888'     888   888  d8(  888   888   888 o.  )88b 888    .o  `88.     .8'   888  
8""88888P'     .8'     o888o o888o `Y888""8o  888bod8P' 8""888P' `Y8bod8P'     `YbodP'     o888o 
           .o..P'                             888                                              
           `Y8P'                             o888o                                              
```
---
## Getting Started

More info for SynapseUI, because very very early stage. [Introduce for SynapseUI](https://github.com/par274/synapseui/blob/main/.github/introduce.md)

## File Permissions
Some folders need writable permissions for the application to work properly.

```bash
# Create required directories
mkdir -p logs
mkdir -p src/internal/template_cache
mkdir -p src/internal/logs

# Set ownership to web server user
sudo chown -R www-data:www-data logs src/internal/template_cache src/internal/logs

# Set directory permissions to 755 for general directories
sudo find logs src/internal/template_cache src/internal/logs -type d -exec chmod 755 {} \;

# Set file permissions to 644 for any files inside
sudo find logs src/internal/template_cache src/internal/logs -type f -exec chmod 644 {} \;

# Make writable directories (cache/logs) writable by owner and web server
sudo chmod -R 770 logs src/internal/template_cache src/internal/logs
```

## Which LLM Manager's are supported?
Currently fully support for `ollama` and `llama.cpp(llama-swap)`. You have to define it in the `src/.env` file in the `APP_LLM_ADAPTER` variable and choose which one Docker will install.

- [Ollama API](https://github.com/par274/synapseui/tree/main/src/platform/Native/src/Adapters/Ollama)
- [llama.cpp API](https://github.com/par274/synapseui/tree/main/src/platform/Native/src/Adapters/LLamacpp)

But you should know that this will only apply to Docker.

We currently recommend `llama.cpp`. However, this requires providing a model input when preloading `llama.cpp`. To overcome this, we used the `llama-swap` proxy server. This allows us to use any model we want, just like in ollama, and achieve our project goals.

### Fact: so why didn't we use ollama as the default?
Because llama.cpp feels faster and much more customizable now. Ollama is all set, but we still need a "sufficient" amount of customization. Ollama is like the end user, however in this project we want to enable the end user to use the chain model.

## GPU Support
First you must change the `UTILIZATION` cpu(or cuda) to NVIDIA(nvgpu) or AMD ROCm(amdgpu) in `Docker .env` file.

Full support GPU list: https://github.com/ollama/ollama/blob/main/docs/gpu.md

The following steps are for Ubuntu.

## NVIDIA & CPU
* `UTILIZATION=cuda` for both support CPU/GPU in llama.cpp (llama-swap) (recommended)
* `UTILIZATION=nvgpu` for GPU acceleration in Ollama
* `UTILIZATION=cpu` for CPU in Ollama (llama-swap)

If you're on the llama.cpp platform, you can use cuda directly. This allows you to use both the CPU and the NVIDIA GPU simultaneously.
But you need to be careful, if you want to force it to GPU or CPU, you need to define the model like `gemma3:1b_cpu`(or cuda) in functions like `chat()` or `generate()`.

More info for: https://github.com/par274/synapseui/tree/main/.docker/llama-swap/swap-config.yaml

### NVIDIA GPU

If you are going to use NVIDIA GPU, you must install NVIDIA Container Toolkit.

For Ubuntu, Debian (example. Please follow official NVIDIA steps):
https://docs.nvidia.com/datacenter/cloud-native/container-toolkit/latest/install-guide.html

```bash
# Install the prerequisites for the instructions below:
sudo apt-get update && sudo apt-get install -y --no-install-recommends \
   curl \
   gnupg2

# Configure the production repository:
curl -fsSL https://nvidia.github.io/libnvidia-container/gpgkey | sudo gpg --dearmor -o /usr/share/keyrings/nvidia-container-toolkit-keyring.gpg \
  && curl -s -L https://nvidia.github.io/libnvidia-container/stable/deb/nvidia-container-toolkit.list | \
    sed 's#deb https://#deb [signed-by=/usr/share/keyrings/nvidia-container-toolkit-keyring.gpg] https://#g' | \
    sudo tee /etc/apt/sources.list.d/nvidia-container-toolkit.list

# Optionally, configure the repository to use experimental packages:
sudo sed -i -e '/experimental/ s/^#//g' /etc/apt/sources.list.d/nvidia-container-toolkit.list

# Update the packages list from the repository:
sudo apt-get update

# Install the NVIDIA Container Toolkit packages:
export NVIDIA_CONTAINER_TOOLKIT_VERSION=1.18.0-1
  sudo apt-get install -y \
      nvidia-container-toolkit=${NVIDIA_CONTAINER_TOOLKIT_VERSION} \
      nvidia-container-toolkit-base=${NVIDIA_CONTAINER_TOOLKIT_VERSION} \
      libnvidia-container-tools=${NVIDIA_CONTAINER_TOOLKIT_VERSION} \
      libnvidia-container1=${NVIDIA_CONTAINER_TOOLKIT_VERSION}
```

### Configuring Docker
```bash
# Configure the container runtime by using the nvidia-ctk command:
sudo nvidia-ctk runtime configure --runtime=docker

# Restart the Docker daemon: (this is not necessary if you're in WSL)
sudo systemctl restart docker

# For testing
sudo docker run --rm --runtime=nvidia --gpus all ubuntu nvidia-smi
```

### AMD ROCm (this is not testing, not recommend. Please use NVIDIA GPU or CPU.)
`UTILIZATION=amdgpu`

If you use AMD GPU and it has ROCm support, you can use it too. First, you need to install `amdgpu-dkms`. If you are already install ROCm, you have it. So you can skip this step.

```bash
wget https://repo.radeon.com/amdgpu-install/6.4.1/ubuntu/noble/amdgpu-install_6.4.60401-1_all.deb
sudo apt install ./amdgpu-install_6.4.60401-1_all.deb
sudo apt update
sudo apt install python3-setuptools python3-wheel
sudo usermod -a -G render,video $LOGNAME # Add the current user to the render and video groups
sudo apt install rocm
```

And reboot your system.

## Framework
Runs on a custom solution called the Synaptic Framework (Par3).

This application framework is designed around a modular architecture and uses a custom template engine whose syntax is inspired by XenForo 2 and vBulletin, while its template architecture follows the design principles and evolution of Twig. It separates core functionality into independent services managed by a container system, enabling clean organization, extensibility, and the creation of efficient native-style applications. It also aims to integrate React and PHP in a hybrid structure that unifies modern reactive interfaces with traditional server-side execution.

## Packages used in this project
- [Symfony/http-foundation](https://symfony.com/doc/current/components/http_foundation.html)
- [Vlucas/phpdotenv](https://github.com/vlucas/phpdotenv)
- [Nikic/fast-route](https://github.com/nikic/FastRoute)
- [Doctrine/DBAL](https://github.com/doctrine/dbal)

## License

The software in this repository is released under the **GNU General Public License v2 (GPL-2.0) or later** from the Free Software Foundation. You can also [read the text of the license](https://github.com/par274/synapseui/blob/main/license.md).
