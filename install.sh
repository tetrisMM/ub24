# Passo 1: Instalar Updates
echo "Instalando o unzip..."
apt update && apt install unzip -y

# Passo 3: Fazer download do arquivo XUI_1.5.13.zip
echo "Fazendo download do XUI_1.5.13.zip..."
wget -O /tmp/XUI_1.5.13.zip https://tetrispt.eu/ubuntu24/xui/XUI_1.5.13.zip

# Passo 4: Extrair o arquivo ZIP
echo "Extraindo o arquivo XUI_1.5.13.zip..."
unzip /tmp/XUI_1.5.13.zip -d /tmp/

# Passo 5: Tornar o script install executável
echo "Tornando o script 'install' executável..."
chmod +x /tmp/install

# Passo 6: Executar o script install
echo "Executando o script de instalação..."
/tmp/install


echo "Script concluído!"



