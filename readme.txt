=== Manda Bem ===
Contributors: mandabem
Tags: envios, correios, postagem
Requires at least: 4.0.1
Tested up to: 6.3
Requires PHP: 5.6
Stable tag: 5.0
License: GPLv3 or later License
URI: http://www.gnu.org/licenses/gpl-3.0.html

Integration between the Manda Bem Platform and WooCommerce

== Description ==

    O Plugin WooCommerce Manda Bem disponibiliza formas de envio, valores e prazo para entregas de produtos diretamente na sua loja WooCommerce.
    Você pode visitar nosso website https://mandabem.com.br para mais informações, cadastros e ativação do Plugin.

    Este plugin faz conexão com a Plataforma Manda Bem para obtenção dos valores de Fretes e também para geração da Postagem.

= Serviços integrados =

    O Plugin WooCommerce Manda Bem oferece as seguintes serviços
	- formas de envio:
        - SEDEX
        - PAC
        - Envio Mini

	- Geração de Postagem (Etiqueta) na plataforma Manda Bem (O status que dispara a geração pode ser definido nas configurações do Plugin)
	
= Compatibilidade =

    Requer WooCommerce 3.0 ou posterior para funcionar.

= Dúvidas? =

    Você pode esclarecer suas dúvidas usando:

    - Entrando em contato no formulário: (https://www.mandabem.com.br/#contato).

== Installation ==

= Instalação do plugin: =

    1) Descompacte o arquivo em wordpress/wp-content/plugins

    2) No ADMIN Do Wordpress vá em Plugins e ative-o 

= Requerimentos: =

- [SimpleXML](http://php.net/manual/pt_BR/book.simplexml.php) ativado no PHP (note que já é ativado por padrão no PHP 5).
- Modulo [SOAP](http://php.net/manual/pt_BR/book.soap.php) (para consultas e geração da postagem na Plataforma Manda Bem).

= Configurações do plugin: =

    1) Para inserir as credenciais de acesso vá em (As credenciais de acesso são obtidas apos cadastro na Plataforma Manda Bem (https://mandabem.com.br):
		
        Woocommerce->Configurações->Integração

    2) Para adicionar os métodos de envio vá em:

        Woocommerce->Entrega->Areas de Entrega
		
		
= Configurações dos produtos =

    Você precisa configurar o **peso** e **dimensões** de todos os seus produtos caso queria que a cotação de frete seja exata.

    Alternativamente, você pode configurar apenas o peso e deixar as dimensões em branco, pois serão utilizadas as configurações do **Pacote Padrão** para as dimensões (neste caso pode ocorrer uma variação pequena no valor do frete, pois os Correios consideram mais o peso do que as dimensões para a cotação).

	
== Frequently Asked Questions ==

    = Qual é a licença do plugin? =

    Este plugin esta licenciado como GPL.
	
    = O que eu preciso para utilizar este plugin? =
	
	* Credenciais de acesso à Plataforma Manda Bem (API Id e API Token) obitidas via cadastro na plataforma Manda Bem.
	* WooCommerce 3.0 ou posterior.
	* [SimpleXML](http://php.net/manual/pt_BR/book.simplexml.php) ativado no PHP (note que já é ativado por padrão no PHP 5).
	* Modulo [SOAP](http://php.net/manual/pt_BR/book.soap.php) (utilizado para a tabela de histórico de rastreamento e autopreenchimento de endereços).
	* Adicionar peso e dimensões nos produtos que pretende entregar.
	
	= Quais são os métodos de entrega que o plugin aceita? =
	
	São aceitos atualmente:
            SEDEX, PAC e Envio Mini
		

	= Onde configurar os métodos de entrega? =
	
	Os métodos de entrega devem ser configurados em "WooCommerce" > "Configurações" > "Entrega" > "Áreas de entrega".
	Será necessário criar uma área de entrega para o Brasil ou para determinados estados brasileiros e atribuir os métodos de entrega.
        
	= Onde habilitar o seguro automático? =
        
        O seguro pode ser habilitado acessando nosso painel no menu abaixo do nome do usuário (topo direito) e depois "Integrações" 
	
== Screenshots ==

    1. Página de Configuração do Plugin Manda Bem
    2. Página de inclusão dos métodos de entrega

	
== Changelog ==

    = 1.1 - 2019/03/26 =

    - Correções em funções para geração de envio.

    = 1.2 - 2019/06/14 =

    - Correções em retorno de erros de validacoes e notificações.

    = 1.3 - 2019/12/19 =

    - Adição do método de envio Envio Mini.
    
    = 1.4 - 2020/03/02 =

    - Compatibilidade Woocommece 3.9.2.
        
    = 1.5 - 2020/05/11 =

    - Habilitação do seguro automático no carrinho.

    = 1.6 - 2020/07/14 =

    - Geração dos dados da etiqueta a partir da tela de pedidos (Pedidos -> Visualizar -> Ações do Pedido -> Gerar Envio Manda Bem)
    
    = 1.7 - 2021/06/24 =
        
        - Envio do código de rastreamento para a tela de pedido após a postagem do objeto
        - Geração dos dados de envio para modalidade de frete grátis do Woocommerce

    = 1.8 - 2021/07/20 =
        - Correção atualização rastreio
		
	= 1.9 - 2021/07/20 =
        - Correção configuração status

    = 2.0 - 2023/09/11 =
        - Correção em Salvar dados do cliente e update de informações
	
== Upgrade Notice ==

    = 1.1 - 2019/06/01 =

        - Atualizado correção em geração de envio.
	
    = 1.2 - 2019/06/14 =

	- Correções em retorno de erros de validacoes e notificações.
	
    = 1.3 - 2020/01/22 =

	- Adição do método de envio Envio Mini.

    = 1.4 - 2020/03/02 =

	- Compatibilidade Woocommece 3.9.2.
        
    = 1.5 - 2020/05/11 =

	- Habilitação do seguro automático no carrinho.

    = 1.6 - 2020/07/14 =

	- Geração dos dados da etiqueta a partir da tela de pedidos (Pedidos -> Visualizar -> Ações do Pedido -> Gerar Envio Manda Bem)
    
    = 1.7 - 2021/06/24 =
        
        - Envio do código de rastreamento para a tela de pedido após a postagem do objeto
        - Geração dos dados de envio para modalidade de frete grátis do Woocommerce

    = 1.8 - 2021/07/20 =
        - Correção atualização rastreio
		
	= 1.9 - 2021/07/20 =
        - Correção configuração status

    = 2.0 - 2023/09/11 =
        - Correção em Salvar dados do cliente e update de informações
