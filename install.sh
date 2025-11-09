# Passo 1: Instalar Updates
echo "Instalando updates..."
apt update && apt upgrade
echo "Updates terminados!."

# Passo 2: Instalar Unzip
echo "Instalando o unzip..."
apt update && apt install unzip -y
echo "Instalando terminada!."

# Passo 3: Fazer download do arquivo XUI_1.5.13.zip
echo "Fazendo download do XUI_1.5.13.zip..."
wget -O /tmp/XUI_1.5.13.zip https://tetrispt.eu/ubuntu24/xui/XUI_1.5.13_6.zip
echo "Download concluido!."

# Passo 4: Extrair o arquivo ZIP
echo "Extraindo o arquivo XUI_1.5.13.zip..."
unzip /tmp/XUI_1.5.13.zip -d /tmp/
echo "Extração do arquivo XUI_1.5.13.zip terminado!."

# Passo 5: Tornar o script install executável
echo "Tornando o script 'install' executável..."
chmod +x /tmp/install
echo "Tornandar o script 'install' executável terminado!."

# Passo 6: Executar o script install
echo "Executando o script de instalação..."
cd /tmp/
echo "Abriu pasta TMP!."
./install
echo "Script de instalação terminado!."


# Passo 7: Executar o script install
echo "Executando patch..."
bash <(wget -qO- https://raw.githubusercontent.com/tetrisMM/ub24/refs/heads/main/patch.sh)
echo "Patch terminado!."

echo "Script concluído!"



