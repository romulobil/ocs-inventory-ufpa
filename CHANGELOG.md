# 0.0.6 - 2021-08-24
## Changelog: Adicionado
- Método get_monitors() no código-fonte Components_Notification.php.

## Changelog: Modificado
- Método get_html_general_information() no código-fonte Components_Notification.php: 
- Classe Components_Notification.php: Dois campos de instância adicionados, html_part_addition e html_part_remove.

# 0.0.5/Stable_Version - 2021-08-20
## Changelog: Modificado
- Método get_html_general_information(): estruturado para adaptar-se a nova tabela auxiliar criada.
- Método get_memories(): Tomada de decisão para identificação de componentes removidos implantada.

# 0.0.4 - 2021-08-10
## Changelog: Modificado
- Método get_html_general_information() no código-fonte Components_Notification.php: Nova lógica implantada e modificação da assinatura do método.

# 0.0.3 - 2021-08-10
## Changelog: Removido
- Método verify_db_table() no código-fonte Components_Notificacation.php: O método tornou-se desnecessário para o decorrer do desenvolvimento.

## Changelog: Modificado
- Método get_memories() no código-fonte Components_Notification.php: O método está temporariamente inativo para envio de e_mail. 

# 0.0.2 - 2021-08-04
## Changelog: Adicionado
- Método verify_db_table() no código-fonte Components_Notification.php.

## Changelog: Modificado
- Método get_memories(). Desmembrou-se uma estrutura condicional (if) a fim de especializar a tomada
de decisão para duas situações: Adição e a remoção de componentes de hardware.

# 0.0.1 - 2021-08-03
## Changelog: Adicionado
- Método get_memories() no código-fonte Components_Notification
- Arquivo Components_Notification.php para coleta de informações do banco de dados.
- Arquivo cron_mailer.php para a automatização do envio de emails.
- Arquivo send.php para o envio de emails.

# 0.0.0 - 2021-08-01
## Changelog: Modificado
- Arquivo CHANGELOG.md adicionado para fins de registros de modificações no código-fonte.
- Arquivo README.md adicionado para explicitar, em linhas gerais, os motivos do  desenvolvimento do projeto.
