# A web-based and modular with graph/node-powered AI interface

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
# Getting Started

### GPU Support
First you need change `OLLAMA_UTILIZATION` CPU to GPU. And install NVIDIA Container Toolkit.

```bash
curl -fsSL https://nvidia.github.io/libnvidia-container/gpgkey | sudo gpg --dearmor -o /usr/share/keyrings/nvidia-container-toolkit-keyring.gpg \
  && curl -s -L https://nvidia.github.io/libnvidia-container/stable/deb/nvidia-container-toolkit.list | \
    sed 's#deb https://#deb [signed-by=/usr/share/keyrings/nvidia-container-toolkit-keyring.gpg] https://#g' | \
    sudo tee /etc/apt/sources.list.d/nvidia-container-toolkit.list
sudo apt-get update
sudo apt-get install -y nvidia-container-toolkit

# Configure NVIDIA Container Toolkit
sudo nvidia-ctk runtime configure --runtime=docker
sudo systemctl restart docker

# Test GPU integration
docker run --gpus all nvidia/cuda:11.5.2-base-ubuntu20.04 nvidia-smi
```