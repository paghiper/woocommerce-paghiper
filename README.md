# WooCommerce - Módulo de boleto PagHiper 

Permite a emissão de boletos e integração do gateway da Paghiper ao seu WooCommerce.
Este módulo implementa emissão de boletos com retorno automático.

* **Versão mais Recente:** 1.0
* **Requer WooCommerce** versão mínima 2.2.0
* **Requer Wordpress** preferencialmente atualizado
* **Requisitos:** PHP >= 5.2.0, cURL ativado.
* **Compatibilidade:** Wordpress 7.1.2, PHP 7.x, HHVM. Integrado diretamente ao Wordpress usando WC_API


# Como Instalar

1. Crie sua conta na PagHiper [clique aqui para saber como](https://github.com/paghiper/whmcs/wiki/Como-criar-seu-cadastro-na-PagHiper).

2. Baixe [este arquivo .zip](https://github.com/paghiper/woocommerce-paghiper/archive/master.zip), crie uma pasta chamada 'woocommerce-paghiper' dentro da sua pasta de plugins, extraia os arquivos do .zip e faça upload dentro da pasta criada.

3. Dentro da área administrativa do seu Wordpress, vá em: Woocommerce > Configurações > Finalizar Compra. Haver um item escrito "Boleto Bancário", com o ID paghiper. Clique neste item.

4. Ative o Boleto PagHiper marcando a primeira opção e preencha o restante do formulário com seu e-mail de cadastro da PagHiper e seu Token.

5. Configure a quantidade de dias que deseja dar de prazo no vencimento e ative o checkout transparente.

Se tiver dúvidas sobre esse processo, acesse nosso [guia de configuração de plugin](https://github.com/paghiper/whmcs/wiki/Configurando-o-plugin-no-seu-WHMCS)


# Suporte

Para questões relacionadas a integração e plugin, acesse o [forum de suporte no Github](https://github.com/paghiper/whmcs/issues);
Para dúvidas comerciais e/ou sobre o funcionamento do serviço, visite a nossa [central de atendimento](https://www.paghiper.com/atendimento/).

# Changelog

## Planejado para a próxima versão

* Reutilização de boletos ao invés de emitir um novo a cada acesso
* Emissão antecipada de boletos (automaticamente, no momento da criação do pedido)
* Disponibilização de linha digitável no painel e e-mails de cobrança/fatura


## 1.0 - 2017/04/13

* Lançamento inicial

# Licença

Copyright 2016 Serviços Online BR.

Licensed under the 3-Clause BSD License (the "License"); you may not use this file except in compliance with the License. You may obtain a copy of the License at

[https://opensource.org/licenses/BSD-3-Clause](https://opensource.org/licenses/BSD-3-Clause)

Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language governing permissions and limitations under the License.
