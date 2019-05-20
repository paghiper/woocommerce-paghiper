=== WooCommerce - Módulo de boleto PagHiper ===
Contributors: henriqueccruz, paghiper
Tags: woocommerce, boleto, paghiper, pagamento
Requires at least: 3.5
Tested up to: 4.9.6
Stable tag: 3.6.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Implementa a emissão de boletos e integração do gateway da Paghiper ao seu WooCommerce.


# WooCommerce - Módulo de boleto PagHiper 

Permite a emissão de boletos e integração do gateway da Paghiper ao seu WooCommerce.
Este módulo implementa emissão de boletos com retorno automático.

* **Versão mais Recente:** 1.2.6
* **Requer WooCommerce** versão mínima 2.2.0
* **Requer Wordpress** preferencialmente atualizado
* **Requisitos:** PHP >= 5.2.0, cURL ativado.
* **Compatibilidade:** Wordpress 4.9.x, Woocommerce 3.3.x, PHP 7.x, HHVM. Integrado diretamente ao Wordpress usando WC_API


# Como Instalar

1. Crie sua conta na PagHiper [clique aqui para saber como](https://github.com/paghiper/whmcs/wiki/Como-criar-seu-cadastro-na-PagHiper).

2. Baixe [este arquivo .zip](https://github.com/paghiper/woocommerce-paghiper/archive/master.zip), crie uma pasta chamada 'woocommerce-paghiper' dentro da sua pasta de plugins, extraia os arquivos do .zip e faça upload dentro da pasta criada.

3. Dentro da área administrativa do seu Wordpress, vá em: Woocommerce > Configurações > Finalizar Compra. Haver um item escrito "Boleto Bancário", com o ID paghiper. Clique neste item.

4. Ative o Boleto PagHiper marcando a primeira opção e preencha o restante do formulário com seu e-mail de cadastro da PagHiper e seu Token.

5. Configure a quantidade de dias que deseja dar de prazo no vencimento e ative o checkout transparente.

Se tiver dúvidas sobre esse processo, acesse nosso [guia de configuração de plugin](https://github.com/paghiper/woocommerce-paghiper/wiki/Configurando-o-plugin-no-seu-WHMCS)


# Suporte

Para questões relacionadas a integração e plugin, acesse o [forum de suporte no Github](https://github.com/paghiper/woocommerce-paghiper/issues);
Para dúvidas comerciais e/ou sobre o funcionamento do serviço, visite a nossa [central de atendimento](https://www.paghiper.com/atendimento/).

== Changelog ==

= Planejado para a próxima versão =

- Emissão antecipada de boletos (automaticamente, no momento da criação do pedido)
- Disponibilização de linha digitável no painel e e-mails de cobrança/fatura
- Boleto PDF anexado nos e-mails do pedido
- Envio de e-mails de lembrete automatizados pelo Woocommerce, com comunicação da loja para maior conversão
- Implementação de funcionalidade de boleto parcelado

= 1.2.6 - 2018/06/12 =

- MELHORIA: Telas de erro implementadas em mais alguns casos
- MELHORIA: Lançamento no repositório oficial do WP, permitindo instalação direto pelo painel
- MELHORIA: Implementação da API HTTP do Wordpress, para melhoria de performance e padronização

 = 1.2.5.3 - 2018/04/27 =

- BUGFIX: Melhoria na lógica de exibição das telas de status, caso a data de vencimento do boleto ja tenha passado

 = 1.2.5 - 2018/04/06 =

- MELHORIA: Re-utilização de boletos emitidos
- MELHORIA: Telas de status do pedido (Pago, Aguardando e Cancelado), caso o cliente acesse o boleto diretamente, depois da data de vencimento (evitando confusão por parte dos clientes).
- Fix: Não é mais possível que o cliente faça re-emitissão de boletos cancelados, a não ser que o lojista mude a data de vencimento pelo painel.
- Fix: Strings não-traduzidas e melhorias no texto dos avisos
- BUGFIX: Inconsistência na data de vencimento, caso a data seja mais de 25 dias da data atual

= 1.2.4.2 - 2017/12/20 =

- BUGFIX: ID do Pedido inconsistente
Caso: Comportamento inconsistente na função intval()
A função pode retornar o ID do pedido com um espaço na frente, em alguns casos.

= 1.2.4.1 - 2017/10/10 =

- Corridos detalhes relacionados a texto e tradução.

= 1.2.4 - 2017/10/08 =

- Melhorias no sistema de atualização e tradução de algumas strings.

= 1.2.3.1 - 2017/09/15 =

- Fix: Corrige eventual problema com cancelamento de pedidos com boletos já pagos.

= 1.2.3 - 2017/09/13 =

- Fix: Hooks eram mostrados ao ativar o plug-in.

= 1.2.2 - 2017/09/03

- Fix: Baixa do estoque só acontecia em boletos com status diferentes de "Aguardando"
- ESTABILIDADE: Suporte a baixa de estoque no WC em versões inferiores a 2.7

= 1.2.1 - 2017/08/31 =

- Melhoria: Incremento de estoque no cancelamento de boletos. Ficará para uso disponível na próxima versão.
- Melhoria: Pedidos a partir de agora ficam "Aguardando" quando o cliente escolhe boleto PagHiper.
- Fix: E-mails só eram enviados quando o cliente acessava o boleto pela primeira vez
- ESTABILIDADE: Baixa do estoque acontecia quantas vezes o cliente acessava o boleto

 = 1.2 - 2017/07/07 =

- Correção de bugs e estabiliadade do plug-in
- BUG: Data de crédito ficava indefinida na compensação do boleto
- BUG: Pedido podia ser marcado como "Aguardando" ou "Cancelado", caso o cliente gerasse mais de uma fatura e não pagasse a mais recente.
- ESTABILIDADE: Implementação de lógica para impedir que boletos não pagos alterem status de pedidos ja aprovados.

= 1.1.1 - 2017/07/07 =

- Atualização no updater

= 1.1 - 2017/04/13 =

- Repositório renomeado

= 1.0 - 2017/04/13 =

- Lançamento inicial

# Licença

Copyright 2016 Serviços Online BR.

Licensed under the 3-Clause BSD License (the "License"); you may not use this file except in compliance with the License. You may obtain a copy of the License at

[https://opensource.org/licenses/BSD-3-Clause](https://opensource.org/licenses/BSD-3-Clause)

Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language governing permissions and limitations under the License.
