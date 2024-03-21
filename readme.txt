=== WooCommerce Boleto e PIX PagHiper ===
Contributors: henriqueccruz
Donate link: https://www.paghiper.com/
Tags: woocommerce, pix, boleto, paghiper, pagamento, gateway
Requires at least: 6.0
Tested up to: 6.5.0
Stable tag: trunk
Requires PHP: 7.2
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Ofereça a seus clientes pagamento boleto bancário com a PagHiper. Fácil, prático e rapido!

== Description ==

Ofereça a seus clientes pagamento boleto bancário com a PagHiper. Fácil, prático e rapido!
Emita boletos bancários de maneira descomplicada. A PagHiper cuida de toda a emissão, compensação e registro do boleto.
O plug-in anexa o boleto, mostra código de barras e linha digitável nos e-mails de pedido e ainda faz reposição de estoque, caso o boleto não seja pago.

Fácil, prático e rápido!

* **Versão mais Recente:** 2.3.3
* **Requer WooCommerce** versão mínima 4.0.0
* **Requer Wordpress** preferencialmente atualizado
* **Requisitos:** PHP >= 7.2, cURL ativado.
* **Compatibilidade:** Wordpress 6.5.0, Woocommerce 8.7.0, PHP 8.2.0.


# Como Instalar

1. Crie sua conta na PagHiper [clicando aqui](https://www.paghiper.com/abra-sua-conta/);

2. Faça login e guarde suas **Chave de API (ApiKey)** e **Token** em Minha Conta > Credenciais;

3. No painel do seu site Wordpress, acesse a seção de plug-ins e clique em **Adicionar novo**. Digite "PagHiper" e aperte Enter;

4. Dentro da área administrativa do seu Wordpress, vá em: Woocommerce > Configurações > Finalizar Compra. Haver um item escrito "Boleto Bancário", com o ID paghiper. Clique neste item;

5. Ative o Boleto PagHiper marcando a primeira opção e preencha o restante do formulário com seu e-mail de cadastro da PagHiper e seu Token;

6. Configure a quantidade de dias que deseja dar de prazo no vencimento e comece a receber!

**Boas vendas!**


# Suporte

Para questões relacionadas a integração e plugin, acesse o [forum de suporte no Github](https://github.com/paghiper/woocommerce-paghiper/issues);
Para dúvidas comerciais e/ou sobre o funcionamento do serviço, visite a nossa [central de atendimento](https://www.paghiper.com/atendimento/).

== Changelog ==

= Planejado para a próxima versão =

- Envio de e-mails de lembrete automatizados pelo Woocommerce, com comunicação da loja para maior conversão
- Implementação de funcionalidade de boleto parcelado

## 2.3.3

- Suporte a HPOS
- Maior estabilidade na operação
- Erro fatal ao instalar plug-in em alguns ambientes
- Remoção de múltiplos warnings e erros

## 2.3.2

- Bugfix: Using $this out of context ao processar notificações IPN
- Bugfix: Alguns casos ainda podiam falhar na comparação de data entre pedido e transação ao determinar data de vencimento
- Bugfix: Mais assertividade ao lidar com resposta ao prompt de avaliação do plug-in

## 2.3.1

- Melhoria: Mais clareza e mais informações nos logs
- Bugfix: Duplicação de transações em alguns casos (timezone)
- Bugfix: Nome de transação (PIX/boleto) incorretos no painel ou nas telas de erro

## 2.3

- Melhoria: Suporte a emissão de PIX/boleto para pedidos com status malsucedido (failed)
- Bugfix: Múltiplas transações geradas para o mesmo pedido
- Bugfix: Conversão implícita de float para int
- BUgfix: URL de notificação IPN dinâmica

## 2.2.2

- Bugfix: A depender do checkout da loja, telefone do cliente poderia ser ignorado ao criar uma nova transação (PIX ou boleto)

## 2.2.1

- Bugfix: Botão de copiar código PIX não copia em alguns casos

## 2.2.0

- Melhoria: Compatibilidade total com PHP8
- Melhoria: Segunda via de PIX e boleto no painel de pedido
- Melhoria: Gramática e texto otimizados no checkout
- Melhoria: Compatibilidade com AutomateWoo para envio de lembretes personalizados
- Melhoria: Melhorias de acessibilidade no checkout
- Melhoria: Novo shortcode para uso em checkouts personalizados
- Melhoria: Agora o plug-in é compatível com Ninja checkout, NextMove e customizadores em geral
- Bugfix: Warnings e deprecated errors removidos
- Bugfix: Botão de copiar código PIX não indicava ação após clique
- Bugfix: Conflito de bibliotecas jQuery (jQuery Mask)
- Bugfix: Dados do cliente geravam transação sem número de telefone
- Bugfix: Problemas relacionados ao composer e bibliotecas carregadas
- Bugfix: Checkout desalinhado no mobile
- Bugfix: Mais entradas de log para debug
- Bugfix: Operadores de finais de semana não atuam mais em transações PIX
- Bugfix: Erro 500 no painel ao atualizar data de vencimento

## 2.1.5

- Melhoria: Mais informações nos logs
- Melhoria: Lógica de re-emissão aprimorada
- Bugfix: Transações (PIX e Boleto) sendo geradas duas vezes na criação do pedido
- Bugfix: Cancelamento de PIX e boleto não mudam mais o status do pedido, mesmo que o método de pagamento tenha sido mudado
- Bugfix: E-mails de nova data de vencimento não eram enviados (dependendo da versão do Woocommerce)
- Bugfix: Controle mais estrito do estoque
- Bugfix: Instruções de pagamento eram mostrados várias vezes, dependendo das condições

## 2.1.4 - 2021/02/14

- Bugfix: Pedido falhava na validação caso o status fosse "Aguardando pagamento"
- Bugfix: Erro ao enviar e-mails de estoque baixo
- Bugfix: Evita baixa duplicada de estoque (e seus transtornos relacionados)
- Melhoria: Evita conflitos com outros plugins usando versões diferentes do GuzzleHttp
- Melhoria: Mais segurança no processamento da transação no checkout

## 2.1.3

- Bugfix: Erro ao editar produtos ou outros Custom post types do Woocommece

## 2.1.2

- Melhoria: Aviso de limite padrão comercial para transações acima de R$ 9.000
- Melhoria: Compatibilidade com plug-ins de multi-step checkout (ou lojas sem AJAX no checkout)
- Melhoria: Remoção de vários notices e warnings
- Melhoria: Compatibilidade com plug-ins de quantidade fracionada
- Bugfix: Boletos estavam sendo emitidos para pedidos não feitos com Paghiper em alguns casos
- Bugfix: Instruções de pagamento eram mostrados várias vezes, dependendo das condições
- Bugfix: Não era possível atualizar a data de vencimento do boleto/PIX via back-end
- Bugfix: Plug-in retornava pedidos para o status de "aguardando" em alguns casos
- Bugfix: Mais segurança na atualização dos pedidos
- Bugfix: Link incorreto para pedidos era formado, caso bloco de boleto fosse acessado da área "meus pedidos"

## 2.1.1

- Bugfix: Credenciais não estavam sendo trazidas da versão anterior

## 2.1.0

- Nova funcionalidade: Receba seus pedidos usando pagamento por PIX
- Melhoria: Campo próprio de CPF/CNPJ (Não é mais necessário uso do Brazilian Market on WooCommerce) e validação
- Melhoria: Checagem de múltiplas instâncias do plugin (desatualizadas ou não)
- Melhoria: Melhor indicação dos descontos do pedido no painel da Paghiper 
- Melhoria: Avisos na área administrativa sobre potenciais problemas de funcionamento
- Melhoria: Informações de pagamento dentro do pedido, na área de "Meus pedidos"
- Melhoria: Opções dos métodos de pagamento disponíveis direto da lista de plug-ins
- Bugfix: Mensagem incorreta nas notas de pedido, ao confirmar pagamento
- Bugfix: Gateways ainda ficavam disponíveis, mesmo para pedidos abaixo do valor mínimo, causando tela branca no checkout

## 2.0.5

- Bugfix: Melhor tratativa de descontos (quando há um item com valor negativo no carrinho)
- Bugfix: Warning removido do painel (Warning: count(): Parameter must be an array or an object that implements Countable...)
- Melhoria: Melhor descrição das notificações IPN no log de pedido

## 2.0.4

- Melhoria: Uso opcional do plug-in Brazilian Market on WooCommerce (antigo WooCommerce Extra Checkout Fields for Brazil)
- Melhoria: UX e acessibilidade na página de finalização de pedido
- Melhoria: Downgrade da versão do GuzzleHttp (evita conflitos com outros plug-ins que também usam a lib)
- Bugfix: Estabilidade e tratamento de erro na emissão e baixa de pagamentos
- Bugfix: Problema com Mimetype e permissões de acesso no gerador de código de barras
- Bugfix: Cálculo de desconto retornava valor incompleto em alguns casos
- Bugfix: Conciliação de estoque (no cancelamento de pedidos)
- Bugfix: Corrige alguns potenciais problemas relacionados a criação de log (dependendo da versão do Woocommerce)

## 2.0.3

- Bugfix: Erro "payer_name invalido" ao finalizar pedido

## 2.0.2

- Validação de ApiKey e avisos no back-end do Wordpress

## 2.0.1

- Lógica de emissão de boleto re-escrita totalmente do zero
- Automação de pedido (boleto emitido automaticamente ao criar o pedido)
- Boleto anexo nos e-mails de notificação da loja
- Código de barras e linha digitável disponíveis na tela de confirmação do pedido e nos e-mails de notificação da loja 
- Melhor lógica de cálculo da data de vencimento na emissão dos boletos
- Melhor segurança ao salvar nova data de vencimento (validação e máscara de preenchimento)
- API 2.0 e novas funcionalidades
- Novos filtros disponíveis para maior personalização
- Implementação da API HTTP do Wordpress, para melhoria de performance e padronização
- Uso do novo PHP SDK
- Lançamento no repositório oficial do WP, permitindo instalação direto pelo painel
- FIX: Plugin agora suporta uso em lojas sem mod_rewrite disponível (links no formato https://loja/index.php/...)


# Licença

Copyright 2016-2020 Serviços Online BR.

Licensed under the 3-Clause BSD License (the "License"); you may not use this file except in compliance with the License. You may obtain a copy of the License at

[https://opensource.org/licenses/BSD-3-Clause](https://opensource.org/licenses/BSD-3-Clause)

Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language governing permissions and limitations under the License.
