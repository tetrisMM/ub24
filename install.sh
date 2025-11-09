# Passo 1: Instalar Updates
echo "Instalando updates..."
apt update && apt upgrade
echo "Updates terminados!."

# Passo 2: Instalar Unzip
echo "Instalando o unzip..."
apt update && apt install unzip -y
echo "Instalando terminada!."

# Passo 2: Instalar Unzip
echo "Instalando o unzip..."
sudo apt install php8.1-curl
sudo apt install php8.2-curl
sudo apt install php8.3-curl
echo "Instalando terminada!."

# Passo 3: Instalar Unzip
echo "Instalando o unzip..."
sudo apt install sshpass -y
echo "Instalando terminada!."

# Passo 4: Fazer download do arquivo XUI_1.5.13.zip
echo "Fazendo download do XUI_1.5.13.zip..."
wget -O /tmp/XUI_1.5.13.zip https://tetrispt.eu/ubuntu24/xui/XUI_1.5.13_13.zip
echo "Download concluido!."

# Passo 5: Extrair o arquivo ZIP
echo "Extraindo o arquivo XUI_1.5.13.zip..."
unzip /tmp/XUI_1.5.13.zip -d /tmp/
echo "Extração do arquivo XUI_1.5.13.zip terminado!."

# Passo 6: Tornar o script install executável
echo "Tornando o script 'install' executável..."
chmod +x /tmp/install
echo "Tornandar o script 'install' executável terminado!."

# Passo 7: Executar o script install
echo "Executando o script de instalação..."
cd /tmp/
echo "Abriu pasta TMP!."
./install
echo "Script de instalação terminado!."


# Passo 8: Executar o script install
echo "Executando patch..."
bash <(wget -qO- https://raw.githubusercontent.com/tetrisMM/ub24/refs/heads/main/patch.sh)
echo "Patch terminado!."

# Passo 9: Fazer download do arquivo XUI_1.5.13.zip
echo "Fazendo download do PHP Telegram..."
wget -O /xui/telegram/telegram_bot.php https://tetrispt.eu/ubuntu24/xui/telegram_bot.php
echo "Download concluido!."

# Passo 10: Fazer download do Fenix2.php
echo "Fazendo download do PHP Backup..."
wget -O /home/xui/crons/fenix.php https://tetrispt.eu/ubuntu24/xui/backup_script.php
echo "Download concluido!."

# Passo 11: Fazer download do Backupnow.php
echo "Fazendo download do PHP Backup Now..."
wget -O /home/xui/admin/backupnow.php https://tetrispt.eu/ubuntu24/xui/backupnow.php
echo "Download concluido!."

# Passo 12: Limpar
echo "Limpar pasta temp..."
rm -rf /tmp/*
echo "Limpeza terminada!."



echo "Script concluído!"



