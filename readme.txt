=== PagHiper Boleto e PIX para WooCommerce ===
Contributors: henriqueccruz
Donate link: https://www.paghiper.com/
Tags: pix, boleto, paghiper, pagamento, gateway
Requires at least: 6.0
Tested up to: 6.8.1
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

* **Versão mais Recente:** 2.4.3
* **Requer WooCommerce** versão mínima 4.0.0
* **Requer Wordpress** preferencialmente atualizado
* **Requisitos:** PHP >= 7.2, cURL ativado.
* **Compatibilidade:** Wordpress 6.8.1, Woocommerce 9.8.5, PHP 8.3.0.


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

## 2.4.3

- Melhoria: Otimizações e refatoração de código em diversas rotas e jornadas
- Melhoria: Mais informações de log
- Fix: Mudanças de status duplicadas
- Fix: Remoção de diversos warnings
- Fix: Metabox não aparece para pedidos com HPOS ativado em algumas circunstâncias
- FIx: Erros ocasionais nas áreas de admin, a depender da combinação de plug-ins utilizada


# Licença

Copyright 2016-2025 Serviços Online BR.

Licensed under the 3-Clause BSD License (the "License"); you may not use this file except in compliance with the License. You may obtain a copy of the License at

[https://opensource.org/licenses/BSD-3-Clause](https://opensource.org/licenses/BSD-3-Clause)

Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language governing permissions and limitations under the License.
