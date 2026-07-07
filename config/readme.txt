Esta psata serve para centralizar todas as definições e ajustes globais do seu projeto.

Em vez de espalhar configurações por dezenas de arquivos, você as coloca lá para que fiquem fáceis de encontrar e alterar.

O que geralmente vai aqui dentro?
	Conexão com Banco de Dados: Host, nome do banco, usuário e senha (database.php).

	Constantes do Sistema: URL base do site, caminhos de pastas ou o nome da aplicação (config.php).

	Segurança: Chaves de API, segredos de tokens JWT ou configurações de criptografia.

	Ambiente: Definição se o site está em modo "Desenvolvimento" (mostra erros) ou "Produção" (esconde erros).

Por que usar?
	Segurança: Facilita na hora de proteger dados sensíveis (você pode dizer ao Git para ignorar um arquivo específico de configuração real via .gitignore).

	Manutenção: Se você trocar de servidor ou mudar a senha do banco, altera apenas um arquivo e o site inteiro continua funcionando.

	Organização: Separa a "lógica" (Controllers) da "infraestrutura" (Configurações).

	Analogia: Pense na pasta config/ como o quadro de energia de uma casa. Se você precisar desligar a luz ou trocar um fusível, sabe exatamente onde ir, sem precisar quebrar as paredes para achar a fiação.
