# Passo 1: Fazer download do arquivo XUI_1.5.13.zip
echo "Fazendo download do XUI_1.5.13.zip..."
wget -O /tmp/XUI_1.5.13.zip http://tetrispt.eu/ubuntu24/xui/XUI_1.5.13.zip

# Passo 2: Instalar o unzip, caso não esteja instalado
echo "Instalando o unzip..."
sudo apt update && sudo apt install -y unzip

# Passo 3: Extrair o arquivo ZIP
echo "Extraindo o arquivo XUI_1.5.13.zip..."
unzip /tmp/XUI_1.5.13.zip -d /tmp/

# Passo 4: Tornar o script install executável
echo "Tornando o script 'install' executável..."
chmod +x /tmp/install

# Passo 5: Executar o script install
echo "Executando o script de instalação..."
/tmp/install

echo "Script concluído!"