/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/elements.js":
/*!*************************!*\
  !*** ./src/elements.js ***!
  \*************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   InlineTaxIdField: () => (/* binding */ InlineTaxIdField)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _use_element_options__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./use-element-options */ "./src/use-element-options.js");
/* harmony import */ var validation_br__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! validation-br */ "./node_modules/validation-br/dist/index.js");





const baseTextInputStyles = 'wc-block-gateway-input paghiper_tax_id p-Input-input Input p-Input-input--textRight';

/**
 * InlineTaxIdField component
 *
 * @param {Object} props Incoming props for the component.
 * @param {React.ReactElement} props.inputErrorComponent
 * @param {function(any):any} props.onChange
 */
const InlineTaxIdField = ({
  inputErrorComponent: ValidationInputError,
  onChange,
  gatewayName
}) => {
  const [isEmpty, setIsEmpty] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useState)(true);
  const [isInvalid, setIsInvalid] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useState)(false);
  const [isComplete, setIsComplete] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useState)(false);
  const [fieldLabel, setFieldLabel] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useState)((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('CPF do Pagador', 'paghiper-payments'));
  const [fieldInput, setFieldInput] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useState)('');
  const {
    options,
    isActive,
    isFocus,
    onActive,
    error,
    setError
  } = (0,_use_element_options__WEBPACK_IMPORTED_MODULE_3__.useElementOptions)({
    hideIcon: true
  });
  const errorCallback = event => {
    if (event.error) {
      setError(event.error.message);
    } else {
      setError('');
    }
    setIsEmpty(event.empty);
    onChange(event);
    if (!event.target.value) {
      setIsEmpty(true);
    }
  };
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useEffect)(() => {
    if (fieldInput.replace(/\D/g, '').length > 11) {
      setFieldLabel((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('CNPJ do Pagador', 'paghiper-payments'));
    } else {
      setFieldLabel((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('CPF do Pagador', 'paghiper-payments'));
    }
    if (!isEmpty) {
      if (!isFocus) {
        if (fieldInput.replace(/\D/g, '').length > 11 && fieldInput.replace(/\D/g, '').length < 14) {
          setError((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('O número do seu CNPJ está incompleto.', 'paghiper-payments'));
          setIsInvalid(true);
        } else if (fieldInput.replace(/\D/g, '').length < 11) {
          setError((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('O número do seu CPF está incompleto.', 'paghiper-payments'));
          setIsInvalid(true);
        }
      } else {
        if (fieldInput.replace(/\D/g, '').length == 11) {
          // Valida CPF
          if (!(0,validation_br__WEBPACK_IMPORTED_MODULE_4__.isCPF)(fieldInput)) {
            setError((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('O número do seu CPF está correto.', 'paghiper-payments'));
            setIsInvalid(true);
          } else {
            setIsComplete(true);
          }
        } else if (fieldInput.replace(/\D/g, '').length == 14) {
          // Valida CNPJ
          if (!(0,validation_br__WEBPACK_IMPORTED_MODULE_4__.isCNPJ)(fieldInput)) {
            setError((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('O número do seu CNPJ não está correto.', 'paghiper-payments'));
            setIsInvalid(true);
          } else {
            setIsComplete(true);
          }
        }
      }
    } else {
      setIsInvalid(false);
      setIsComplete(false);
    }
  }, [fieldInput, isFocus]);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useEffect)(() => {
    setIsInvalid(false);
    setIsComplete(false);
    setError('');
  }, [fieldInput]);
  const taxIdMaskBehavior = (val, e) => {
    return val.replace(/\D/g, '').length > 11 ? '00.000.000/0000-00' : '000.000.000-009';
  };

  // Initialize mask everytime we render the component
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useEffect)(() => {
    if (typeof jQuery('.paghiper_tax_id').mask === "function") {
      jQuery('.paghiper_tax_id').mask(taxIdMaskBehavior, {
        onKeyPress: function (val, e, field, options) {
          field.mask(taxIdMaskBehavior.apply({}, arguments), options);
        }
      });
    } else {
      console.log('Paghiper block failed to initialize TaxID mask');
    }
  }, []);
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(react__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "wc-block-components-form"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "wc-block-gateway-container wc-block-components-text-input wc-inline-tax-id-element paghiper-taxid-fieldset" + (isActive || !isEmpty ? ' is-active' : '')
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    type: "text",
    id: "wc-paghiper-inline-tax-id-element",
    name: "_" + gatewayName + "_cpf_cnpj",
    className: baseTextInputStyles + (isEmpty ? ' empty Input--empty' : '') + (isInvalid ? ' invalid' : '') + (isComplete ? ' valid' : ''),
    onBlur: () => onActive(isEmpty, false),
    onFocus: () => onActive(isEmpty, true),
    onChange: errorCallback,
    onInput: e => setFieldInput(e.target.value),
    "aria-label": fieldLabel,
    required: true,
    title: true
  }), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", {
    htmlFor: "wc-paghiper-inline-tax-id-element"
  }, fieldLabel), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(ValidationInputError, {
    errorMessage: error
  }))));
};

/***/ }),

/***/ "./src/use-element-options.js":
/*!************************************!*\
  !*** ./src/use-element-options.js ***!
  \************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   useElementOptions: () => (/* binding */ useElementOptions)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);


/**
 * @typedef {import('../stripe-utils/type-defs').StripeElementOptions} StripeElementOptions
 */

/**
 * Returns the value of a specific CSS property for the element matched by the provided selector.
 *
 * @param {string} selector     CSS selector that matches the element to query.
 * @param {string} property     Name of the property to retrieve the style
 *                              value from.
 * @param {string} defaultValue Fallback value if the value for the property
 *                              could not be retrieved.
 *
 * @return {string} The style value of that property in the document element.
 */
const getComputedStyle = (selector, property, defaultValue) => {
  let elementStyle = {};
  if (typeof document === 'object' && typeof document.querySelector === 'function' && typeof window.getComputedStyle === 'function') {
    const element = document.querySelector(selector);
    if (element) {
      elementStyle = window.getComputedStyle(element);
    }
  }
  return elementStyle[property] || defaultValue;
};

/**
 * Default options for the stripe elements.
 */
const elementOptions = {
  style: {
    base: {
      iconColor: '#666EE8',
      color: '#31325F',
      fontSize: getComputedStyle('.wc-block-checkout', 'fontSize', '16px'),
      lineHeight: 1.375,
      // With a font-size of 16px, line-height will be 22px.
      '::placeholder': {
        color: '#fff'
      }
    }
  },
  classes: {
    focus: 'focused',
    empty: 'empty',
    invalid: 'has-error'
  }
};

/**
 * A custom hook handling options implemented on the stripe elements.
 *
 * @param {Object} [overloadedOptions] An array of extra options to merge with
 *                                     the options provided for the element.
 *
 * @return {StripeElementOptions}  The stripe element options interface
 */
const useElementOptions = overloadedOptions => {
  const [isActive, setIsActive] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
  const [isFocus, setIsFocus] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
  const [options, setOptions] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)({
    ...elementOptions,
    ...overloadedOptions
  });
  const [error, setError] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)('');
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    const color = isActive ? '#CFD7E0' : '#fff';
    setOptions(prevOptions => {
      let showIcon;
      // forced when disabling co-badged cards (for Sources API)
      if (prevOptions.showIcon === false) {
        showIcon = {
          showIcon: false
        };
      } else if (typeof prevOptions.showIcon !== 'undefined') {
        showIcon = {
          showIcon: isActive
        };
      } else {
        showIcon = {};
      }
      return {
        ...prevOptions,
        style: {
          ...prevOptions.style,
          base: {
            ...prevOptions.style.base,
            '::placeholder': {
              color
            }
          }
        },
        ...showIcon
      };
    });
  }, [isActive]);
  const onActive = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useCallback)((isEmpty, isFocus) => {
    if (!isEmpty) {
      setIsActive(true);
    } else {
      setIsActive(prevActive => !prevActive);
    }
    if (isFocus) {
      setIsFocus(true);
    } else {
      setIsFocus(false);
    }
  }, [setIsActive, setIsFocus]);
  return {
    options,
    isActive,
    isFocus,
    onActive,
    error,
    setError
  };
};

/***/ }),

/***/ "./node_modules/validation-br/dist/cnh.js":
/*!************************************************!*\
  !*** ./node_modules/validation-br/dist/cnh.js ***!
  \************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {


/**
 * isCNH()
 * Calcula se uma CNH é válida
 *
 * @doc
 * CNH deve possuir 11 caracteres
 *
 * - Os caracteres 1 a 9 são números sequenciais.
 *
 *
 * - Os caracteres 10 e 11 são dígitos verificadores.
 *
 * 1) Partes do número
 *  ____________________________ ______
 * |  Número                    | D V |
 * |  5  8  3  1  6  7  9  4  5   3 4 |
 * |____________________________|_____|
 *
 * 2) Cálculo do primeiro DV.
 *
 *  - Soma-se o produto das algarismos 1 a 9 pelos números 2, 3, 4, 5, 6, 7, 8, 9, 10.
 *
 *    5   8   3   1   6   7   9   4   5
 *    x   x   x   x   x   x   x   x   x
 *    2   3   4   5   6   7   8   9   10
 * = 10 +24 +12  +5 +36 +49 +72 +36  +50  = 294
 *
 *  - O somatório encontrado é dividido por 11. O DV1 é 11 subtraído do resto da divisão. Se o
 *    resto for 10, o DV1 é 0.
 *
 * 2.1) 294 / 11 tem resto igual a 8. 11-7 = 3
 *      DV1 = 3
 *
 * 3) Cálculo do segundo DV
 *
 *  - Soma-se o produto das algarismos 1 a 9 juntamente com o 10 caractere
 *    que é o DV1, pelos números 3, 4, 5, 6, 7, 8, 9, 10, 11, 2. O DV1 será
 *    multiplicado por 2 e ficará no final.
 *
 *    5   8   3   1   6   7   9   4   5   3
 *    x   x   x   x   x   x   x   x   x   x
 *    3   4   5   6   7   8   9  10  11   2
 * = 10 +24 +12  +5 +36 +49 +72 +36 +50  +6  =  348
 *
 * 3.1) 348 / 11 tem resto igual a 7. 11 - 7 = 4.
 *      DV2 = 4
 *
 *  - O somatório encontrado é dividido por 11. O DV2 é 11 subtraído do resto da divisão. Se o
 *    resto for 10, o DV2 é 0.
 *
 * Fonte: https://www.devmedia.com.br/forum/validacao-de-cnh/372972
 *
 * @param {String} value Título eleitoral
 * @returns {Boolean}
 */
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", ({ value: true }));
exports.fake = exports.mask = exports.validate = exports.validateOrFail = exports.dv = void 0;
const ValidationBRError_1 = __importDefault(__webpack_require__(/*! ./data/ValidationBRError */ "./node_modules/validation-br/dist/data/ValidationBRError.js"));
const utils_1 = __webpack_require__(/*! ./utils */ "./node_modules/validation-br/dist/utils.js");
/**
 * Calcula o Dígito Verificador de um RENAVAM informado
 *
 * @returns String Número fake de um cnh válido
 */
const dv = (value) => {
    const cnh = (0, utils_1.clearValue)(value, 9, {
        trimAtRight: true,
        rejectEmpty: true,
    });
    const sum1 = (0, utils_1.sumElementsByMultipliers)(cnh.substring(0, 9), [2, 3, 4, 5, 6, 7, 8, 9, 10]);
    const dv1 = (0, utils_1.sumToDV)(sum1);
    const sum2 = (0, utils_1.sumElementsByMultipliers)(cnh.substring(0, 9) + dv1, [3, 4, 5, 6, 7, 8, 9, 10, 11, 2]);
    const dv2 = (0, utils_1.sumToDV)(sum2);
    return `${dv1}${dv2}`;
};
exports.dv = dv;
/**
 * validateOrFail()
 * Valida se um número é válido e
 * retorna uma exceção se não estiver
 *
 * @param {String|Number} value Número a ser validado
 * @returns {Boolean}
 */
const validateOrFail = (value) => {
    const cnh = (0, utils_1.clearValue)(value, 11, {
        fillZerosAtLeft: true,
        rejectEmpty: true,
        rejectHigherLength: true,
        rejectEqualSequence: true,
    });
    if ((0, exports.dv)(cnh) !== cnh.substring(9, 11)) {
        throw ValidationBRError_1.default.INVALID_DV;
    }
    return true;
};
exports.validateOrFail = validateOrFail;
/**
 * validate()
 * Valida se um número é válido
 *
 * @param {String|Number} value Número a ser validado
 * @returns {Boolean}
 */
const validate = (value) => {
    try {
        return (0, exports.validateOrFail)(value);
    }
    catch (error) {
        return false;
    }
};
exports.validate = validate;
/**
 * Aplica uma máscara a uma string
 *
 * @returns String string com a máscara aplicada
 */
const mask = (value) => (0, utils_1.applyMask)(value, '000000000-00');
exports.mask = mask;
/**
 * Cria um número fake
 *
 * @returns String Número fake porém válido
 */
const fake = (withMask = false) => {
    const value = (0, utils_1.fakeNumber)(9, true);
    const cnh = `${value}${(0, exports.dv)(value)}`;
    if (withMask)
        return (0, exports.mask)(cnh);
    return cnh;
};
exports.fake = fake;
exports["default"] = exports.validate;
//# sourceMappingURL=cnh.js.map

/***/ }),

/***/ "./node_modules/validation-br/dist/cnpj.js":
/*!*************************************************!*\
  !*** ./node_modules/validation-br/dist/cnpj.js ***!
  \*************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {


/**
 * isCNPJ()
 * Calcula se um CNPJ é válido
 *
 * @doc
 * - CNPJ deve possuir 14 dígitos no formato 00.000.000/0000-00
 *
 * - Os caracteres 1 a 8 são números sequenciais definidos pela Receita Federal
 *
 * - Os caracteres 9 a 12 são a identificação das filiais da empresa.
 *
 * - Os caracteres 13 e 14 são os dígitos verificadores
 *
 * 1) Partes do número
 *  _______________________________ _______________ _______
 * | Número                        |    Filiais    |  DV   |
 * | 1   1 . 2   2   2 . 3   3   3 / 0   0   0   1 - X   Y |
 * |_______________________________|_______________|_______|
 *
 * 2) Cálculo do primeiro DV.
 *
 *  - Soma-se o produto das algarismos 1 a 12 pelos números 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2
 *
 *    1   1   2   2   2   3   3   3   0   0   0   1
 *    x   x   x   x   x   x   x   x   x   x   x   x
 *    5   4   3   2   9   8   7   6   5   4   3   2
 * =  5  +4  +6  +4 +18 +24 +21 +18  +0  +0  +0  +2 = 102
 *
 *  - O somatório encontrado é dividido por 11 e o resultado é subtraído de 11
 *    102 / 11 tem resto 8. 11 - 3 = 8. DV1 é 8.
 *    Obs.: Caso o cálculo de DV1 retorne 10, o resultado será 0.
 *
 * 3) Cálculo do segundo DV.
 *
 *  - Soma-se o produto das algarismos 1 a 13 (incluindo o DV1 calculado) pelos
 *    números 6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2.
 *
 *    1   1   2   2   2   3   3   3   0   0   0   1   8
 *    x   x   x   x   x   x   x   x   x   x   x   x   x
 *    6   5   4   3   2   9   8   7   6   5   4   3   2
 * =  6  +5  +8  +6  +4 +27 +24 +21  +0  +0  +0  +3 +16 = 120
 *
 *  - O somatório encontrado é dividido por 11 e o resultado é subtraído de 11
 *    120 / 11 tem resto 10. 11 - 10 = 1. DV2 é 1.
 *    Obs.: Caso o cálculo de DV2 retorne 10, o resultado será 0.
 *
 * Fonte: http://www.macoratti.net/alg_cnpj.htm
 *
 * @param {String} value Título eleitoral
 * @returns {Boolean}
 */
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", ({ value: true }));
exports.validate = exports.validateOrFail = exports.fake = exports.mask = exports.dv = void 0;
const ValidationBRError_1 = __importDefault(__webpack_require__(/*! ./data/ValidationBRError */ "./node_modules/validation-br/dist/data/ValidationBRError.js"));
const utils_1 = __webpack_require__(/*! ./utils */ "./node_modules/validation-br/dist/utils.js");
const dv = (value) => {
    const cnpj = (0, utils_1.clearValue)(value, 12, {
        trimAtRight: true,
        rejectEmpty: true,
    });
    const sum1 = (0, utils_1.sumElementsByMultipliers)(cnpj.substring(0, 12), '543298765432');
    const dv1 = (0, utils_1.sumToDV)(sum1);
    const sum2 = (0, utils_1.sumElementsByMultipliers)(cnpj.substring(0, 12) + dv1, '6543298765432');
    const dv2 = (0, utils_1.sumToDV)(sum2);
    return `${dv1}${dv2}`;
};
exports.dv = dv;
/**
 * Aplica uma máscara ao número informado
 *
 * @param {String} value Número de Processo
 * @returns {String} Valor com a máscara
 */
const mask = (value) => (0, utils_1.applyMask)(value, '00.000.000/0000-00');
exports.mask = mask;
/**
 *
 *
 */
const fake = (withMask = false) => {
    const num = (0, utils_1.fakeNumber)(12, true);
    const cnpj = `${num}${(0, exports.dv)(num)}`;
    if (withMask)
        return (0, exports.mask)(cnpj);
    return cnpj;
};
exports.fake = fake;
/**
 * validateOrFail()
 * Valida se um número é válido e
 * retorna uma exceção se não estiver
 *
 * @param {String|Number} value Número a ser validado
 * @returns {Boolean}
 */
const validateOrFail = (value) => {
    const cnpj = (0, utils_1.clearValue)(value, 14, {
        fillZerosAtLeft: true,
        rejectEmpty: true,
        rejectHigherLength: true,
        rejectEqualSequence: true,
    });
    if ((0, exports.dv)(cnpj) !== cnpj.substring(12, 14)) {
        throw ValidationBRError_1.default.INVALID_DV;
    }
    return true;
};
exports.validateOrFail = validateOrFail;
/**
 * validate()
 * Valida se um número é válido
 *
 * @param {String|Number} value Número a ser validado
 * @returns {Boolean}
 */
const validate = (value) => {
    try {
        return (0, exports.validateOrFail)(value);
    }
    catch (error) {
        return false;
    }
};
exports.validate = validate;
exports["default"] = exports.validate;
//# sourceMappingURL=cnpj.js.map

/***/ }),

/***/ "./node_modules/validation-br/dist/cpf.js":
/*!************************************************!*\
  !*** ./node_modules/validation-br/dist/cpf.js ***!
  \************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {


/**
 * isCPF()
 * Calcula se um CPF é válido
 *
 * @doc
 * CPF deve possuir 11 dígitos.
 *
 * - Os caracteres 1 a 8 são números sequenciais definidos pela Receita Federal
 *
 * - O caractere 9 refere-se à região fiscal emissora do documento
 *    1 – DF, GO, MS, MT e TO
 *    2 – AC, AM, AP, PA, RO e RR
 *    3 – CE, MA e PI
 *    4 – AL, PB, PE, RN
 *    5 – BA e SE
 *    6 – MG
 *    7 – ES e RJ
 *    8 – SP
 *    9 – PR e SC
 *    0 – RS
 *
 * - Os caracteres 10 e 11 são dígitos verificadores.
 *
 * 1) Partes do número
 * ------------------------------------------------
 * | Número                       | R |  DV  |
 *  2   8   0 . 0   1   2 . 3   8   9 - 3   8
 *
 * 2) Cálculo do primeiro DV.
 *
 *  - Soma-se o produto das algarismos 1 a 9 pelos números 10, 9, 8, 7, 6, 5, 4, 3, 2
 *
 *    2   8   0   0   1   2   3   8   9
 *    x   x   x   x   x   x   x   x   x
 *   10   9   8   7   6   5   4   3   2
 * = 20 +72  +0  +0  +6 +10 +12 +24 +18 = 162
 *
 *  - O somatório encontrado é dividido por 11 e o resultado é subtraído de 11
 *    162 / 11 tem resto 8. 11 - 8 = 3. DV1 é 3.
 *    Obs.: Caso o cálculo de DV1 retorne 10, o resultado será 0.
 *
 * 3) Cálculo do segundo DV.
 *
 *  - Soma-se o produto das algarismos 1 a 10 pelos números 11, 10, 9, 8, 7, 6, 5, 4, 3, 2
 *
 *    2   8   0   0   1   2   3   8   9   3
 *    x   x   x   x   x   x   x   x   x   x
 *   11  10   9   8   7   6   5   4   3   2
 * = 22 +80  +0  +0  +7 +12 +15 +32 +27 = 201
 *
 *  - O somatório encontrado é dividido por 11 e o resultado é subtraído de 11
 *    201 / 11 tem resto 3. 11 - 3 = 8. DV2 é 8.
 *    Obs.: Caso o cálculo de DV2 retorne 10, o resultado será 0.
 *
 * Fonte: http://clubes.obmep.org.br/blog/a-matematica-nos-documentos-cpf/
 *
 * @param {String} value Título eleitoral
 * @returns {Boolean}
 */
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", ({ value: true }));
exports.validate = exports.validateOrFail = exports.fake = exports.mask = exports.dv = void 0;
const ValidationBRError_1 = __importDefault(__webpack_require__(/*! ./data/ValidationBRError */ "./node_modules/validation-br/dist/data/ValidationBRError.js"));
const utils_1 = __webpack_require__(/*! ./utils */ "./node_modules/validation-br/dist/utils.js");
/**
 * dv()
 * Calcula o dígito verificador
 *
 * @param {Number|String} value
 * @returns {String}
 */
const dv = (value) => {
    const cpf = (0, utils_1.clearValue)(value, 9, {
        trimAtRight: true,
        rejectEmpty: true,
    });
    const sum1 = (0, utils_1.sumElementsByMultipliers)(cpf, [10, 9, 8, 7, 6, 5, 4, 3, 2]);
    const dv1 = (0, utils_1.sumToDV)(sum1);
    const sum2 = (0, utils_1.sumElementsByMultipliers)(cpf + dv1, [11, 10, 9, 8, 7, 6, 5, 4, 3, 2]);
    const dv2 = (0, utils_1.sumToDV)(sum2);
    return `${dv1}${dv2}`;
};
exports.dv = dv;
/**
 * Aplica uma máscara ao número informado
 *
 * @param {String} value Número de Processo
 * @returns {String} Valor com a máscara
 */
const mask = (value) => (0, utils_1.applyMask)(value, '000.000.000-00');
exports.mask = mask;
/**
 * fake()
 * Gera um número válido
 *
 * @returns {String}
 */
const fake = (withMask = false) => {
    const num = (0, utils_1.fakeNumber)(9, true);
    const cpf = `${num}${(0, exports.dv)(num)}`;
    if (withMask)
        return (0, exports.mask)(cpf);
    return cpf;
};
exports.fake = fake;
/**
 * validateOrFail()
 * Valida se um número é válido e
 * retorna uma exceção se não estiver
 *
 * @param {String|Number} value Número a ser validado
 * @returns {Boolean}
 */
const validateOrFail = (value) => {
    const cpf = (0, utils_1.clearValue)(value, 11, {
        fillZerosAtLeft: true,
        rejectEmpty: true,
        rejectHigherLength: true,
        rejectEqualSequence: true,
    });
    if ((0, exports.dv)(cpf) !== cpf.substring(9, 11)) {
        throw ValidationBRError_1.default.INVALID_DV;
    }
    return true;
};
exports.validateOrFail = validateOrFail;
/**
 * validate()
 * Valida se um número é válido
 *
 * @param {String|Number} value Número a ser validado
 * @returns {Boolean}
 */
const validate = (value) => {
    try {
        return (0, exports.validateOrFail)(value);
    }
    catch (error) {
        return false;
    }
};
exports.validate = validate;
exports["default"] = exports.validate;
//# sourceMappingURL=cpf.js.map

/***/ }),

/***/ "./node_modules/validation-br/dist/data/ValidationBRError.js":
/*!*******************************************************************!*\
  !*** ./node_modules/validation-br/dist/data/ValidationBRError.js ***!
  \*******************************************************************/
/***/ ((__unused_webpack_module, exports) => {


Object.defineProperty(exports, "__esModule", ({ value: true }));
class ValidationBRError extends Error {
}
exports["default"] = ValidationBRError;
ValidationBRError.INVALID_DV = new ValidationBRError('Dígito verificador inválido');
ValidationBRError.EMPTY_VALUE = new ValidationBRError('Valor não preenchido');
ValidationBRError.MAX_LEN_EXCEDEED = new ValidationBRError('Número de caracteres excedido');
ValidationBRError.SEQUENCE_REPEATED = new ValidationBRError('Sequência de números repetidos não permitida');
//# sourceMappingURL=ValidationBRError.js.map

/***/ }),

/***/ "./node_modules/validation-br/dist/index.js":
/*!**************************************************!*\
  !*** ./node_modules/validation-br/dist/index.js ***!
  \**************************************************/
/***/ ((__unused_webpack_module, exports, __webpack_require__) => {


Object.defineProperty(exports, "__esModule", ({ value: true }));
exports.isNUP17 = exports.isTituloEleitor = exports.isRenavam = exports.isPostalCode = exports.isPIS = exports.isJudicialProcess = exports.isCPF = exports.isCNPJ = exports.isCNH = void 0;
const cnh_1 = __webpack_require__(/*! ./cnh */ "./node_modules/validation-br/dist/cnh.js");
const cnpj_1 = __webpack_require__(/*! ./cnpj */ "./node_modules/validation-br/dist/cnpj.js");
const cpf_1 = __webpack_require__(/*! ./cpf */ "./node_modules/validation-br/dist/cpf.js");
const judicialProcess_1 = __webpack_require__(/*! ./judicialProcess */ "./node_modules/validation-br/dist/judicialProcess.js");
const nup17_1 = __webpack_require__(/*! ./nup17 */ "./node_modules/validation-br/dist/nup17.js");
const pisPasep_1 = __webpack_require__(/*! ./pisPasep */ "./node_modules/validation-br/dist/pisPasep.js");
const postalCode_1 = __webpack_require__(/*! ./postalCode */ "./node_modules/validation-br/dist/postalCode.js");
const renavam_1 = __webpack_require__(/*! ./renavam */ "./node_modules/validation-br/dist/renavam.js");
const tituloEleitor_1 = __webpack_require__(/*! ./tituloEleitor */ "./node_modules/validation-br/dist/tituloEleitor.js");
const isCNH = (value) => (0, cnh_1.validate)(value);
exports.isCNH = isCNH;
const isCNPJ = (value) => (0, cnpj_1.validate)(value);
exports.isCNPJ = isCNPJ;
const isCPF = (value) => (0, cpf_1.validate)(value);
exports.isCPF = isCPF;
const isJudicialProcess = (value) => (0, judicialProcess_1.validate)(value);
exports.isJudicialProcess = isJudicialProcess;
const isPIS = (value) => (0, pisPasep_1.validate)(value);
exports.isPIS = isPIS;
const isPostalCode = (value) => (0, postalCode_1.validate)(value);
exports.isPostalCode = isPostalCode;
const isRenavam = (value) => (0, renavam_1.validate)(value);
exports.isRenavam = isRenavam;
const isTituloEleitor = (value) => (0, tituloEleitor_1.validate)(value);
exports.isTituloEleitor = isTituloEleitor;
const isNUP17 = (value) => (0, nup17_1.validate)(value);
exports.isNUP17 = isNUP17;
exports["default"] = {
    isCNH: exports.isCNH,
    isCNPJ: exports.isCNPJ,
    isCPF: exports.isCPF,
    isJudicialProcess: exports.isJudicialProcess,
    isPIS: exports.isPIS,
    isPostalCode: exports.isPostalCode,
    isRenavam: exports.isRenavam,
    isTituloEleitor: exports.isTituloEleitor,
    isNUP17: exports.isNUP17,
};
//# sourceMappingURL=index.js.map

/***/ }),

/***/ "./node_modules/validation-br/dist/judicialProcess.js":
/*!************************************************************!*\
  !*** ./node_modules/validation-br/dist/judicialProcess.js ***!
  \************************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {


/**
 * judicialProcess
 * Valida, mascara e cria números de processos judiciais
 *
 * @link
 * https://juslaboris.tst.jus.br/bitstream/handle/20.500.12178/30318/2008_res0065_cnj_rep01.pdf?sequence=2
 * http://ghiorzi.org/DVnew.htm#f
 *
 * @doc
 * Os  números de processos judiciais são usados a partir de 2010 para unificar a
 * numeração de processos no Brasil e são usados em todos os tribunais.
 *
 * O número do processo, sem os caracteres especiais, devem possuir até 20 números
 * e deve seguir o padrão abaixo:
 *
 * 1) Partes do número
 *  0002080-25.2012.5.15.0049
 *  NNNNNNN-DD.AAAA.J.TR.OOOO
 * |______|__|____|_|__|____|
 *    |    |   |  | |   |----> Unidade de origem do processo com 4 caracteres
 *    |    |   |  | |--------> TR=Tribunal do segmento do poder judiciário com 2 caracteres
 *    |    |   |  |----------> J=Órgão do poder Judiciário com 1 caractere
 *    |    |   |-------------> Ano do ajuizamento do processo com 4 caracteres
 *    |    |-----------------> Dígito verificador com 2 caracteres
 *    |----------------------> Número sequencial do Processo, por unidade de
 *                             origem, reiniciado anualmente com 7 caracteres
 *
 * Órgãos do Poder Judiciário
 * 1 - Supremo Tribunal Federal
 * 2 - Conselho Nacional de Justiça
 * 3 - Superior Tribunal de Justiça
 * 4 - Justiça Federal
 * 5 - Justiça do Trabalho
 * 6 - Justiça Eleitoral
 * 7 - Justiça Militar da União
 * 8 - Justiça dos Estados e do Distrito Federal e Territórios
 * 9 - Justiça Militar Estadual
 *
 *
 * 2) Dígito Verificador
 *
 * O algoritmo usado para o cálculo do DV chama-se Módulo 97 de Base 10 (ISO 7064).
 *
 * Nota: O número do processo possui 20 caracteres e ultrapassa o tamanho máximo
 * do inteiro em javascript, impedindo que façamos o cálculo diretamente, desta
 * forma, será nacessária uma fatoração para que o resultado seja o correto.
 *
 * 2.1) Cálculo do DV
 * - Caso o DV seja conhecido, ele precisa ser removido do número e colocado
 * como "00" ao final. Caso não esteja incluso no número, adicione '00' ao final.
 *
 * Ex.: O processo "00020802520125150049", cujo dv é "25", será calculado como
 * "000208020125150049" e receberá "00" ao final. O número usado para o cálculo
 * do DV será "00020802012515004900"
 *
 * 2.2) Etapas de Cálculo
 *
 * 00020802012515004900
 *                   ↓↓
 *                   DV ao final como "00"
 *
 * - Aplicamos o MOD 97 aos caracteres de 0 a 7 para calcular a primeira parte
 * part1 = 0002080 % 97 = 43
 *
 * - Concatenamos part1 ao ano, órgão do poder judiciário e tribunal e aplicamos o MOD 97
 * para obtermos o valor da part2
 * part2 = ( part1 +''+ 2012 +''+ 5 +''+ 15 ) % 97 = 26
 *
 * - Concatemos part2 ao código do órgão de origem e ao "00" do final e aplicamos
 * o MOD 97 ao resultado
 * part3 = ( part2 + '0049' + '00') % 97 = 73
 *
 * - Subtraímos o resultado de 98
 * dv = 98 - 73 = 25
 *
 * O Dígito verificador é 25 e deve ser aplicado após o 7º caractere do número do processo
 *
 * Fonte: https://juslaboris.tst.jus.br/bitstream/handle/20.500.12178/30318/2008_res0065_cnj_rep01.pdf?sequence=2
 */
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", ({ value: true }));
exports._getSubCourt = exports.validate = exports.validateOrFail = exports.fake = exports.mask = exports.dv = void 0;
const ValidationBRError_1 = __importDefault(__webpack_require__(/*! ./data/ValidationBRError */ "./node_modules/validation-br/dist/data/ValidationBRError.js"));
const utils_1 = __webpack_require__(/*! ./utils */ "./node_modules/validation-br/dist/utils.js");
/**
 *
 *
 */
const dv = (value) => {
    const judicialProcess = (0, utils_1.clearValue)(value, 18, { trimAtRight: true, rejectEmpty: true });
    const num = judicialProcess.substring(0, 7);
    const yearAndCourt = judicialProcess.substring(7, 14);
    const origin = judicialProcess.substring(14, 18);
    return String(98 - (Number(`${Number(`${Number(num) % 97}${yearAndCourt}`) % 97}${origin}00`) % 97)).padStart(2, '0');
};
exports.dv = dv;
/**
 * Aplica uma máscara ao número informado
 *
 * @param {String} value Número de Processo
 * @returns {String} Valor com a máscara
 */
const mask = (value) => (0, utils_1.applyMask)(value, '0000000-00.0000.0.00.0000');
exports.mask = mask;
/**
 *
 *
 */
const fake = (withMask = false) => {
    const num = (0, utils_1.fakeNumber)(7, true);
    const year = new Date().getFullYear() - +(0, utils_1.fakeNumber)(1);
    let courte1 = (0, utils_1.fakeNumber)(1, true); // Não pode ser '0'
    courte1 = courte1 === '0' ? '1' : courte1;
    const courte2 = _getSubCourt();
    const courte = `${courte1}${courte2}`;
    const origin = (0, utils_1.fakeNumber)(4, true);
    const judicialProcess = `${num}${year}${courte}${origin}`;
    const digits = (0, exports.dv)(judicialProcess);
    const finalNumber = (0, utils_1.insertAtPosition)(judicialProcess, digits, 7);
    if (withMask)
        return (0, exports.mask)(finalNumber);
    return finalNumber;
};
exports.fake = fake;
/**
 * validateOrFail()
 * Valida se um número é válido e
 * retorna uma exceção se não estiver
 *
 * @param {String} value Número a ser validado
 * @returns {Boolean}
 */
const validateOrFail = (value) => {
    const judicialProcess = (0, utils_1.clearValue)(value, 20, {
        fillZerosAtLeft: true,
        rejectEmpty: true,
        rejectHigherLength: true,
    });
    const processWithoutDV = (0, utils_1.removeFromPosition)(judicialProcess, 7, 9);
    if (processWithoutDV.substring(11, 12) === '0') {
        throw new Error('Código do Órgão Judiciário não pode ser "0"');
    }
    if ((0, exports.dv)(processWithoutDV) !== judicialProcess.substring(7, 9)) {
        throw ValidationBRError_1.default.INVALID_DV;
    }
    return true;
};
exports.validateOrFail = validateOrFail;
/**
 * validate()
 * Valida se um número é válido
 *
 * @param {String} value Número a ser validado
 * @returns {Boolean}
 */
const validate = (value) => {
    try {
        return (0, exports.validateOrFail)(value);
    }
    catch (error) {
        return false;
    }
};
exports.validate = validate;
// ////////////////////////////////////////////
//
// Funções auxiliares
//
// ////////////////////////////////////////////
/**
 * Gera um número fake da sub corte de acordo com as regras:
 * - Precisa ser uma string numérica de 2 dígitos.
 * - Não pode ser '0'. CAso seja zero, mude para '01'.
 *
 * A função aceita um parâmetro para viabilizar os testes. Caso
 * não seja definido, será gerado aleatoriamente.
 *
 * @param
 *
 */
function _getSubCourt(courte = undefined) {
    courte = courte !== null && courte !== void 0 ? courte : (0, utils_1.fakeNumber)(2, true).toString();
    return +courte === 0 ? '01' : courte;
}
exports._getSubCourt = _getSubCourt;
exports["default"] = exports.validate;
//# sourceMappingURL=judicialProcess.js.map

/***/ }),

/***/ "./node_modules/validation-br/dist/nup17.js":
/*!**************************************************!*\
  !*** ./node_modules/validation-br/dist/nup17.js ***!
  \**************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {


/**
 * isFederalProtocol()
 * Calcula se é um número válido de protocolo do Governo Federal Brasileiro
 *
 * @doc
 * O Número Unificado de Protocolo de processos do Governo Federal, também conhecido
 * como NUP17, deve ter 17 caracteres, incluindo o dígito verificador de 2 caracteres.
 *
 * 1) Partes do número
 *
 * - Os caracteres 1 a 5 são um código do órgão que gerou o protocolo.
 *
 * - Os caracteres 6 a 11 são o número sequencial do protocolo, sendo que
 * cada órgão emissor tem sua própria sequência e esta é reiniciada a cada ano.
 *
 * - Os caracteres 12 a 15 são referentes ao ano de protocolo
 *
 * - Os caracteres 16 a 17 são referentes ao Dígito Verificador
 *
 * 1.2) Exemplo
 * ---------------------------------------------------------------
 * |  Código do órgão |   Número Sequencial   |    Ano     | D  V
 *  2   3   0   3   7 . 0   0   1   4   6   2 / 2  0  2  1 - 6  5
 *
 * 2) Cálculo do primeiro DV.
 *
 *  - Soma-se o produto das algarismos 1 a 15 pelos números
 *    16, 15, 14, 13, 12, 11, 10, 9, 8, 7, 6, 5, 4, 3, 2
 *
 *    2   3   0   3   7   0   0   1   4   6   2   2   0   2   1
 *    x   x   x   x   x   x   x   x   x   x   x   x   x   x   x
 *   16  15  14  13  12  11  10   9   8   7   6   5   4   3   2
 * = 32 +45  +0 +39 +84  +0  +0  +9 +32 +42 +12 +10  +0  +6  +2 = 313
 *
 *  - O somatório encontrado é dividido por 11. O resto da divisão é subtraído de 11.
 *    313 / 11 tem resto 5. 11 - 5 = 6. DV1 é 6.
 *    Obs.: Caso o cálculo de DV1 retorne 10, o resultado será 0. Caso retorne 11, o DV
 *    será 1. Ou seja, se for maior ou igual a 10, desconsidere a casa das dezenas
 *
 * 3) Cálculo do segundo DV.
 *
 * - Acrescenta o valor do DV1 ao número e faz o somatório dos produtos pelos números
 *   17, 16, 15, 14, 13, 12, 11, 10, 9, 8, 7, 6, 5, 4, 3, 2
 *
 *    2   3   0   3   7   0   0   1   4   6   2   2   0   2   1   6
 *    x   x   x   x   x   x   x   x   x   x   x   x   x   x   x   x
 *   17  16  15  14  13  12  11  10   9   8   7   6   5   4   3   2
 * = 34 +48  +0 +42 +91  +0  +0 +10 +36 +48 +14 +12  +0  +8  +3 +12 = 358
 *
 *  - O somatório encontrado é dividido por 11. O resto da divisão é subtraído de 11.
 *    358 / 11 tem resto 6. 11 - 6 = 1. DV1 é 5.
 *    Obs.: Caso o cálculo de DV1 retorne 10, o resultado será 0. Caso retorne 11, o DV
 *    será 1. Ou seja, se for maior ou igual a 10, desconsidere a casa das dezenas.
 *
 * = DV = 65
 *
 * Fonte: https://www.gov.br/compras/pt-br/acesso-a-informacao/legislacao/portarias/portaria-interministerial-no-11-de-25-de-novembro-de-2019
 *
 * @param {String} value Título eleitoral
 * @returns {Boolean}
 */
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", ({ value: true }));
exports.validate = exports.validateOrFail = exports.fake = exports.mask = exports.dv = void 0;
const ValidationBRError_1 = __importDefault(__webpack_require__(/*! ./data/ValidationBRError */ "./node_modules/validation-br/dist/data/ValidationBRError.js"));
const utils_1 = __webpack_require__(/*! ./utils */ "./node_modules/validation-br/dist/utils.js");
/**
 * dv()
 * Calcula o dígito verificador
 *
 * @param {String} value
 * @returns {String}
 */
const dv = (value) => {
    const nup = (0, utils_1.clearValue)(value, 15, { rejectEmpty: true, trimAtRight: true });
    const nupReverse = nup.split('').reverse().join('');
    const sum1 = (0, utils_1.sumElementsByMultipliers)(nupReverse, [...Array(15)].map((_, i) => i + 2));
    const dv1 = _specificSumToDV(sum1);
    const sum2 = (0, utils_1.sumElementsByMultipliers)(dv1 + nupReverse, [...Array(16)].map((_, i) => i + 2));
    const dv2 = _specificSumToDV(sum2);
    return `${dv1}${dv2}`;
};
exports.dv = dv;
/**
 * Aplica uma máscara ao número informado
 *
 * @param {String} value Número de Processo
 * @returns {String} Valor com a máscara
 */
const mask = (value) => (0, utils_1.applyMask)(value, '00000.000000/0000-00');
exports.mask = mask;
/**
 * fake()
 * Gera um número válido
 *
 * @param {Boolean} withMask Define se o número deve ser gerado com ou sem máscara
 * @returns {String}
 */
const fake = (withMask = false) => {
    const num = (0, utils_1.fakeNumber)(15, true);
    const nup = `${num}${(0, exports.dv)(String(num))}`;
    if (withMask)
        return (0, exports.mask)(nup);
    return nup;
};
exports.fake = fake;
/**
 * validateOrFail()
 * Valida se um número é válido e
 * retorna uma exceção se não estiver
 *
 * @param {String} value Número a ser validado
 * @returns {Boolean}
 */
const validateOrFail = (value) => {
    const nup = (0, utils_1.clearValue)(value, 17, {
        rejectEmpty: true,
        rejectHigherLength: true,
    });
    if ((0, exports.dv)(nup) !== nup.substring(15, 17)) {
        throw ValidationBRError_1.default.INVALID_DV;
    }
    return true;
};
exports.validateOrFail = validateOrFail;
/**
 * validate()
 * Valida se um número é válido
 *
 * @param {String} value Número a ser validado
 * @returns {Boolean}
 */
const validate = (value) => {
    try {
        return (0, exports.validateOrFail)(value);
    }
    catch (error) {
        return false;
    }
};
exports.validate = validate;
exports["default"] = exports.validate;
function _specificSumToDV(sum) {
    const rest = 11 - (sum % 11);
    const exceptions = [
        { rest: 11, dv: 1 },
        { rest: 10, dv: 0 },
    ];
    const inExceptions = exceptions.find((item) => item.rest === rest);
    return !inExceptions ? rest : inExceptions.dv;
}
//# sourceMappingURL=nup17.js.map

/***/ }),

/***/ "./node_modules/validation-br/dist/pisPasep.js":
/*!*****************************************************!*\
  !*** ./node_modules/validation-br/dist/pisPasep.js ***!
  \*****************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {


/**
 * isPIS()
 * Calcula se um código de PIS/PASEP/NIS/NIT no formato 268.27649.96-0 é válido. Não
 * valida o formato, portanto, 26827649960 é equivalente a 268.27649.96-0 para efeitos
 * desta validação.
 *
 * @doc
 * - O número de PIS deve possuir 11 caracteres
 *
 * - Os caracteres de 1 a 10 são a numeração documento
 *
 * - O caractere 11 é o dígito verificador.
 *
 * 1) Partes do número
 *  _______________________________________________
 * |  Número                                 | D V |
 * |  2   6   8 . 2   7   6   4   9 . 9   6  -  0  |
 * |_________________________________________|_____|
 *
 * 2) Cálculo do DV.
 *
 *  - Soma-se o produto das algarismos 3 a 10 pelos números 3, 2, 9, 8, 7, 6, 5, 4, 3, 2
 *
 *    2   6   8   2   7   6   4   9   9   6
 *    x   x   x   x   x   x   x   x   x   x
 *    3   2   9   8   7   6   5   4   3   2
 * =  6 +12 +72 +16 +49 +12 +20 +36 +27 +12  =  234
 *
 *  - O somatório encontrado é dividido por 11 e o resultado é subtraído de 11
 *    234 / 11 tem resto 3. 11 - 3 = 8. DV1 é 8.
 *    Obs.: Caso o cálculo de DV1 retorne 0, o resultado será 5.
 *          Caso retorne 1, o resto será 0
 *
 *
 *
 *
 * Fonte: http://www.macoratti.net/alg_pis.htm
 *
 * @param {String} value Objeto postal no formato 268.27649.96-0
 * @returns {Boolean}
 */
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", ({ value: true }));
exports.validate = exports.validateOrFail = exports.fake = exports.mask = exports.dv = void 0;
const ValidationBRError_1 = __importDefault(__webpack_require__(/*! ./data/ValidationBRError */ "./node_modules/validation-br/dist/data/ValidationBRError.js"));
const utils_1 = __webpack_require__(/*! ./utils */ "./node_modules/validation-br/dist/utils.js");
/**
 * dv()
 * Calcula o dígito verificador
 *
 * @param {Number|String} value
 * @returns {String}
 */
const dv = (value) => {
    const pis = (0, utils_1.clearValue)(value, 10, {
        trimAtRight: true,
        rejectEmpty: true,
    });
    const sum = (0, utils_1.sumElementsByMultipliers)(pis, [3, 2, 9, 8, 7, 6, 5, 4, 3, 2]);
    return String((0, utils_1.sumToDV)(sum));
};
exports.dv = dv;
/**
 * Aplica uma máscara ao número informado
 *
 * @param {String} value Número de Processo
 * @returns {String} Valor com a máscara
 */
const mask = (value) => (0, utils_1.applyMask)(value, '000.00000.00-0');
exports.mask = mask;
/**
 * fake()
 * Gera um número válido
 *
 * @returns {String}
 */
const fake = (withMask = false) => {
    const num = (0, utils_1.fakeNumber)(10, true);
    const pis = `${num}${(0, exports.dv)(num)}`;
    if (withMask)
        return (0, exports.mask)(pis);
    return pis;
};
exports.fake = fake;
/**
 * validateOrFail()
 * Valida se um número é válido e
 * retorna uma exceção se não estiver
 *
 * @param {String|Number} value Número a ser validado
 * @returns {Boolean}
 */
const validateOrFail = (value) => {
    const pis = (0, utils_1.clearValue)(value, 11, {
        fillZerosAtLeft: true,
        rejectEmpty: true,
        rejectHigherLength: true,
        rejectEqualSequence: true,
    });
    if ((0, exports.dv)(pis) !== pis.substring(10, 11)) {
        throw ValidationBRError_1.default.INVALID_DV;
    }
    return true;
};
exports.validateOrFail = validateOrFail;
/**
 * validate()
 * Valida se um número é válido
 *
 * @param {String|Number} value Número a ser validado
 * @returns {Boolean}
 */
const validate = (value) => {
    try {
        return (0, exports.validateOrFail)(value);
    }
    catch (error) {
        return false;
    }
};
exports.validate = validate;
exports["default"] = exports.validate;
//# sourceMappingURL=pisPasep.js.map

/***/ }),

/***/ "./node_modules/validation-br/dist/postalCode.js":
/*!*******************************************************!*\
  !*** ./node_modules/validation-br/dist/postalCode.js ***!
  \*******************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {


/**
 * isPostalCode()
 * Calcula se um código de rastreamento postal no formato JT194690698BR é válido.
 *
 * @doc
 * - O número de registro postal deve possuir 13 caracters no formato JT194690698BR.
 *
 * - Os caracteres 1 e 2 informam o tipo do objeto. Ex.: SX é Sedex, RC é carta registrada etc.
 *
 * - Os caracteres de 3 a 10 são a numeração sequencial do tipo do objeto.
 *
 * - O caractere 11 é o dígito verificador.
 *
 * - Os caracteres 12 e 13 representa o código do País de onde a postagem partiu.
 *
 * 1) Partes do número
 *  ______ ___________________________ ______ _______
 * | Tipo | Número                    |  DV  |  País |
 * | J T    1  9  4  6  9  0  6  9       8      B R  |
 * |______|___________________________|______|_______|
 *
 * 2) Cálculo do DV.
 *
 *  - Soma-se o produto das algarismos 3 a 10 pelos números 8, 6, 4, 2, 3, 5, 9, 7
 *
 *    1   9   4   6   9   0   6   9
 *    x   x   x   x   x   x   x   x
 *    8   6   4   2   3   5   9   7
 * =  8 +54 +16 +12 +18  +0 +54 +63 = 234
 *
 *  - O somatório encontrado é dividido por 11 e o resultado é subtraído de 11
 *    234 / 11 tem resto 3. 11 - 3 = 8. DV1 é 8.
 *    Obs.: Caso o cálculo de DV1 retorne 0, o resultado será 5.
 *          Caso retorne 1, o resto será 0
 *
 *
 *
 *
 * Fonte:
 *
 * @param {String} value Objeto postal no formato JT194690698BR
 * @returns {Boolean}
 */
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", ({ value: true }));
exports.validate = exports.validateOrFail = exports.fake = exports.mask = exports.dv = void 0;
const ValidationBRError_1 = __importDefault(__webpack_require__(/*! ./data/ValidationBRError */ "./node_modules/validation-br/dist/data/ValidationBRError.js"));
const utils_1 = __webpack_require__(/*! ./utils */ "./node_modules/validation-br/dist/utils.js");
/**
 * dv()
 * Calcula o dígito verificador
 *
 * @param {Number|String} value
 * @returns {String}
 */
const dv = (value) => {
    if (!value)
        throw ValidationBRError_1.default.EMPTY_VALUE;
    const postalCode = String(value)
        .replace(/[^0-9]+/gi, '')
        .padStart(8, '0')
        .substring(0, 8);
    const sum = (0, utils_1.sumElementsByMultipliers)(postalCode, [8, 6, 4, 2, 3, 5, 9, 7]);
    const rest = sum % 11;
    // const specificities = { 0: { dv: 5 }, 1: { dv: 0 } }
    const specificities = [
        { rest: 0, dv: 5 },
        { rest: 1, dv: 0 },
    ];
    const specifity = specificities.find((item) => item.rest === rest);
    const DV = specifity ? specifity.dv : 11 - rest;
    return String(DV);
};
exports.dv = dv;
/**
 * Aplica uma máscara ao número informado
 *
 * @param {String} value Número de Processo
 * @returns {String} Valor com a máscara
 */
const mask = (value) => String(value).toLocaleUpperCase();
exports.mask = mask;
/**
 * fake()
 * Gera um número válido
 *
 * @returns {String}
 */
const fake = (withMask = false) => {
    const num = (0, utils_1.fakeNumber)(8, true);
    const postalCode = `${(0, utils_1.randomLetter)()}${(0, utils_1.randomLetter)()}${num}${(0, exports.dv)(num)}BR`;
    if (withMask)
        return (0, exports.mask)(postalCode);
    return postalCode;
};
exports.fake = fake;
/**
 * validateOrFail()
 * Valida se um número é válido e
 * retorna uma exceção se não estiver
 *
 * @param {String} value Número a ser validado
 * @returns {Boolean}
 */
const validateOrFail = (value) => {
    if (!/^[a-z]{2}([\d]{9})[a-z]{2}$/gi.test(String(value))) {
        throw new Error('O número não está no formato "XX000000000XX"');
    }
    const postalCode = (0, utils_1.clearValue)(value.substring(2, 11), 9);
    if ((0, exports.dv)(value.substring(2, 11)) !== postalCode.substring(8, 9)) {
        throw ValidationBRError_1.default.INVALID_DV;
    }
    return true;
};
exports.validateOrFail = validateOrFail;
/**
 * validate()
 * Valida se um número é válido
 *
 * @param {String} value Número a ser validado
 * @returns {Boolean}
 */
const validate = (value) => {
    try {
        return (0, exports.validateOrFail)(value);
    }
    catch (error) {
        return false;
    }
};
exports.validate = validate;
exports["default"] = exports.validate;
//# sourceMappingURL=postalCode.js.map

/***/ }),

/***/ "./node_modules/validation-br/dist/renavam.js":
/*!****************************************************!*\
  !*** ./node_modules/validation-br/dist/renavam.js ***!
  \****************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {


/**
 * RENAVAM
 * Funções auxiliares para cálculo de máscaras, validação, dígito verificador e criaçãode
 * números fake.
 *
 * @doc
 * - O número de RENAVAM deve possuir 11 caracteres
 *
 * - Os caracteres de 1 a 10 são a numeração documento
 *
 * - O caractere 11 é o dígito verificador.
 *
 * 1) Partes do número
 *  _______________________________________________
 * |  Número                                 | D V |
 * |  2   6   8   2   7   6   4   9   9   6  -  0  |
 * |_________________________________________|_____|
 *
 * 2) Cálculo do DV.
 *
 *  - Soma-se o produto das algarismos 3 a 10 pelos números 3, 2, 9, 8, 7, 6, 5, 4, 3, 2
 *
 *    2   6   8   2   7   6   4   9   9   6
 *    x   x   x   x   x   x   x   x   x   x
 *    3   2   9   8   7   6   5   4   3   2
 * =  6 +12 +72 +16 +49 +12 +20 +36 +27 +12  =  234
 *
 *  - O somatório encontrado é multiplicado por 10 e ao resultado
 *    é aplicado o cálculo do MOD 11.
 *
 *    ( 234 * 10 ) / 11 tem resto 8. DV = 8. Caso o resto seja maior ou igual a
 *    10, DV será 0.
 *
 *
 */
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", ({ value: true }));
exports.validate = exports.validateOrFail = exports.fake = exports.mask = exports.dv = void 0;
const ValidationBRError_1 = __importDefault(__webpack_require__(/*! ./data/ValidationBRError */ "./node_modules/validation-br/dist/data/ValidationBRError.js"));
const utils_1 = __webpack_require__(/*! ./utils */ "./node_modules/validation-br/dist/utils.js");
/**
 * dv()
 * Calcula o dígito verificador
 *
 * @param {Number|String} value
 * @returns {String}
 */
const dv = (value) => {
    const renavam = (0, utils_1.clearValue)(value, 10, {
        fillZerosAtLeft: true,
        trimAtRight: true,
        rejectEmpty: true,
    });
    const sum1 = (0, utils_1.sumElementsByMultipliers)(renavam, [3, 2, 9, 8, 7, 6, 5, 4, 3, 2]) * 10;
    const dv1 = sum1 % 11 >= 10 ? 0 : sum1 % 11;
    return `${dv1}`;
};
exports.dv = dv;
/**
 * Aplica uma máscara ao número informado
 *
 * @param {String} value Número de Processo
 * @returns {String} Valor com a máscara
 */
const mask = (value) => (0, utils_1.applyMask)(value, '0000000000-0');
exports.mask = mask;
/**
 * fake()
 * Gera um número válido
 *
 * @returns {String}
 */
const fake = (withMask = false) => {
    const value = (0, utils_1.fakeNumber)(10, true);
    const renavam = `${value}${(0, exports.dv)(value)}`;
    if (withMask)
        return (0, exports.mask)(renavam);
    return renavam;
};
exports.fake = fake;
/**
 * validateOrFail()
 * Valida se um número é válido e
 * retorna uma exceção se não estiver
 *
 * @param {String|Number} value Número a ser validado
 * @returns {Boolean}
 */
const validateOrFail = (value) => {
    const renavam = (0, utils_1.clearValue)(value, 11, {
        fillZerosAtLeft: true,
        rejectEmpty: true,
        rejectHigherLength: true,
        rejectEqualSequence: true,
    });
    if ((0, exports.dv)(renavam) !== renavam.substring(10, 11)) {
        throw ValidationBRError_1.default.INVALID_DV;
    }
    return true;
};
exports.validateOrFail = validateOrFail;
/**
 * validate()
 * Valida se um número é válido
 *
 * @param {String|Number} value Número a ser validado
 * @returns {Boolean}
 */
const validate = (value) => {
    try {
        return (0, exports.validateOrFail)(value);
    }
    catch (error) {
        return false;
    }
};
exports.validate = validate;
exports["default"] = exports.validate;
//# sourceMappingURL=renavam.js.map

/***/ }),

/***/ "./node_modules/validation-br/dist/tituloEleitor.js":
/*!**********************************************************!*\
  !*** ./node_modules/validation-br/dist/tituloEleitor.js ***!
  \**********************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {


/**
 * isTitulo()
 * Calcula se um título eleitoral é válido
 *
 * @doc
 * Título de eleitor deve possuir 12 dígitos.
 *
 * - Os caracteres 1 a 8 são números sequenciais.
 *
 * - Os caracteres 9 e 10 representam os estados da federação onde o título
 *   foi emitido (01 = SP, 02 = MG, 03 = RJ, 04 = RS, 05 = BA, 06 = PR, 07 = CE, 08 = PE,
 *   09 = SC, 10 = GO,  11 = MA12 = PB, 13 = PA, 14 = ES, 15 = PI, 16 = RN, 17 = AL,
 *   18 = MT, 19 = MS, 20 = DF, 21 = SE, 22 = AM, 23 = RO, 24 = AC, 25 = AP, 26 = RR,
 *   27 = TO, 28 = Exterior(ZZ).
 *
 * - Os caracteres 11 e 12 são dígitos verificadores.
 *
 * 1) Partes do número
 * ------------------------------------------------
 * |       Número Sequencial      |  UF   |   DV  |
 *  1   0   2   3   8   5   0   1   0   6   7   1
 *
 * 2) Cálculo do primeiro DV.
 *
 *  - Soma-se o produto das algarismos 1 a 8 pelos números 2, 3, 4, 5, 6, 7, 8 e 9.
 *
 *   1   0   2   3   8   5   0   1
 *   x   x   x   x   x   x   x   x
 *   2   3   4   5   6   7   8   9
 * = 2 + 0 + 8 +15 +48 +35 + 0 + 9  = 117
 *
 *  - O somatório encontrado é dividido por 11. O DV1 é o resto da divisão. Se o
 *    resto for 10, o DV1 é 0.
 *
 * 2.1) 117 / 11 tem resto igual a 7.
 *
 * 3) Cálculo do segundo DV
 *
 * - Soma-se o produto dos algarismos 9 a 11 (relativos aos 2 dígitos da UF e o novo
 *   DV1 que acabou de ser calculado) e os multiplicam pelos números 7, 8 e 9. Se o
 *   resto for 10, DV2 será 0.
 *   0   6   7
 *   x   x   x
 *   7   8   9
 * = 0 +48 +63 = 111
 *
 * 3.1) 111 / 11 tem resto igual a 1.
 *
 * Fonte: http://clubes.obmep.org.br/blog/a-matematica-nos-documentos-titulo-de-eleitor/
 *
 * @param {String} value Título eleitoral
 * @returns {Boolean}
 */
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", ({ value: true }));
exports.validate = exports.validateOrFail = exports.fake = exports.mask = exports.dv = void 0;
const ValidationBRError_1 = __importDefault(__webpack_require__(/*! ./data/ValidationBRError */ "./node_modules/validation-br/dist/data/ValidationBRError.js"));
const utils_1 = __webpack_require__(/*! ./utils */ "./node_modules/validation-br/dist/utils.js");
/**
 * dv()
 * Calcula o dígito verificador
 *
 * @param {Number|String} value
 * @returns {String}
 */
const dv = (value) => {
    const titulo = (0, utils_1.clearValue)(value, 10, {
        fillZerosAtLeft: true,
        trimAtRight: true,
        rejectEmpty: true,
    });
    const sum1 = (0, utils_1.sumElementsByMultipliers)(titulo.substring(0, 8), [2, 3, 4, 5, 6, 7, 8, 9]);
    const dv1 = sum1 % 11 >= 10 ? 0 : sum1 % 11;
    const sum2 = (0, utils_1.sumElementsByMultipliers)(titulo.substring(8, 10) + dv1, [7, 8, 9]);
    const dv2 = sum2 % 11 >= 10 ? 0 : sum2 % 11;
    return `${dv1}${dv2}`;
};
exports.dv = dv;
/**
 * Aplica uma máscara ao número informado
 *
 * @param {String} value Número de Processo
 * @returns {String} Valor com a máscara
 */
const mask = (value) => (0, utils_1.applyMask)(value, '0000.0000.0000');
exports.mask = mask;
/**
 * fake()
 * Gera um número válido
 *
 * @returns {String}
 */
const fake = (withMask = false) => {
    const num = (0, utils_1.fakeNumber)(8, true);
    const uf = (Math.random() * 27 + 1).toFixed(0).padStart(2, '0');
    const titulo = `${num}${uf}${(0, exports.dv)(num + uf)}`;
    if (withMask)
        return (0, exports.mask)(titulo);
    return titulo;
};
exports.fake = fake;
/**
 * validateOrFail()
 * Valida se um número é válido e
 * retorna uma exceção se não estiver
 *
 * @param {String|Number} value Número a ser validado
 * @returns {Boolean}
 */
const validateOrFail = (value) => {
    const titulo = (0, utils_1.clearValue)(value, 12, {
        fillZerosAtLeft: true,
        rejectEmpty: true,
        rejectHigherLength: true,
        rejectEqualSequence: true,
    });
    if ((0, exports.dv)(titulo) !== titulo.substring(10, 12)) {
        throw ValidationBRError_1.default.INVALID_DV;
    }
    return true;
};
exports.validateOrFail = validateOrFail;
/**
 * validate()
 * Valida se um número é válido
 *
 * @param {String|Number} value Número a ser validado
 * @returns {Boolean}
 */
const validate = (value) => {
    try {
        return (0, exports.validateOrFail)(value);
    }
    catch (error) {
        return false;
    }
};
exports.validate = validate;
exports["default"] = exports.validate;
//# sourceMappingURL=tituloEleitor.js.map

/***/ }),

/***/ "./node_modules/validation-br/dist/utils.js":
/*!**************************************************!*\
  !*** ./node_modules/validation-br/dist/utils.js ***!
  \**************************************************/
/***/ (function(__unused_webpack_module, exports, __webpack_require__) {


var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", ({ value: true }));
exports.randomLetter = exports.applyMask = exports.removeFromPosition = exports.insertAtPosition = exports.clearValue = exports.fakeNumber = exports.sumElementsByMultipliers = exports.invalidListGenerator = exports.sumToDV = void 0;
const ValidationBRError_1 = __importDefault(__webpack_require__(/*! ./data/ValidationBRError */ "./node_modules/validation-br/dist/data/ValidationBRError.js"));
/**
 * Calcula o DV verificador a partir das regras do MOD11:
 * O valor da soma é dividido por 11. O resultado é o resto da divisão. Caso o resto seja
 * menor que 2, ou seja, o valor da divisão seja 10 ou 11, o resultado é 0.
 *
 * @param {Integer} sum Soma
 * @returns {Integer}
 */
function sumToDV(sum) {
    return sum % 11 < 2 ? 0 : 11 - (sum % 11);
}
exports.sumToDV = sumToDV;
/**
 * Cria uma lista de valores repetidos no tamanho do documento para eliminar
 * valores que já conhecemos como inválidos
 *
 * @example
 * invalidListGenerator(10, 11)
 * //-> [00000000000, 11111111111, ....., 99999999999]
 *
 * @param {Integer} length Número de itens do array
 * @param {Integer} size Tamanho da string gerada
 * @returns {Array} Lista de valores
 */
function invalidListGenerator(size) {
    return [...Array(10).keys()].map((f) => String(f).repeat(size));
}
exports.invalidListGenerator = invalidListGenerator;
/**
 * Multiplica os elementos de uma string com os elementos de outra, ou de um array
 * e soma o resultado ao final
 *
 * @example
 *   sumElementsByMultipliers('123', '987')      //-> 46
 *   sumElementsByMultipliers('123', [9, 8, 7])  //-> 46
 *
 * @param {String} value
 * @param {String|Array} multiplier
 * @returns {Integer} Somatório
 */
function sumElementsByMultipliers(value, multiplier) {
    if (typeof multiplier === 'string')
        multiplier = multiplier.split('').map((n) => Number(n));
    return multiplier.reduce((accu, curr, i) => accu + curr * Number(value.charAt(i)), 0);
}
exports.sumElementsByMultipliers = sumElementsByMultipliers;
/**
 * fakeNumber()
 * Cria um número aleatório com o número de caracteres
 *
 * @example
 * fakeNumber(8, true) // -> 00083159
 * fakeNumber(4) // -> 831
 *
 * @param {Integer} length
 * @param {Boolean} forceLength Adiciona zeros à esquerda para ter os números de caractes exatos
 * @returns {String}
 */
function fakeNumber(length, forceLength = false) {
    const value = Math.floor(Math.random() * 10 ** length);
    if (forceLength)
        return String(value).padStart(length, '0');
    return +value;
}
exports.fakeNumber = fakeNumber;
/**
 * Limpa um número informado, retirando caracteres diferentes de números,
 * preenchendo com zeros à esquerda se for menor que o tamanho exato e
 * removendo uma parte do número se for maior que tamanho definido.
 *
 * 1) Retira caracteres não-numéricos
 * 2) Preenche com zeros à esquerda se 'value' for menor que 'length'
 * 3) Remove caracteres à direita se 'value' for maior que 'length'
 *
 * @example
 *  clearValue(12345-6, 6) // -> 123456
 *  clearValue(12345678, 3) // -> 123
 *  clearValue(12345, 10) // -> 0000001234
 *
 * @param {Number|String} value
 * @param {Number} length Tamanho exato. Se for null, só retira os caracteres não-numéricos
 * @returns {String} Número com o tamanho exato
 */
function clearValue(value, length = null, options) {
    let clearedValue = String(value).replace(/([/.-]+)/gi, '');
    if (options) {
        if (options.rejectEmpty === true && clearedValue.length === 0) {
            throw ValidationBRError_1.default.EMPTY_VALUE;
        }
        if (options.rejectHigherLength === true && length && clearedValue.length > length) {
            throw ValidationBRError_1.default.MAX_LEN_EXCEDEED;
        }
        if (options.rejectEqualSequence === true && length) {
            const invalidList = invalidListGenerator(length);
            if (invalidList.includes(clearedValue)) {
                throw ValidationBRError_1.default.SEQUENCE_REPEATED;
            }
        }
        // if (!length || clearedValue.length === length) return clearedValue
        if (length && options.fillZerosAtLeft)
            clearedValue = clearedValue.padStart(length, '0');
        if (length && options.trimAtRight)
            clearedValue = clearedValue.substring(0, length);
    }
    return clearedValue;
}
exports.clearValue = clearValue;
/**
 * insertAtPosition()
 * Insere um conjunto de caracteres em um local específico de uma string
 *
 * @example
 * insertAtPosition('AAABBB', 'C', 3) // -> AAACBBB
 * insertAtPosition('000011122223445555', 99, 7) // -> 00001119922223445555
 *
 * @param {String|Number} value Valor original
 * @param {String|Number} insertValue Valor que será inserido
 * @param {String|Number} position Posição que receberá o novo valor
 * @returns {String}
 *
 */
function insertAtPosition(value, insertValue, position) {
    return `${value.substring(0, position)}${insertValue}${value.substring(position)}`;
}
exports.insertAtPosition = insertAtPosition;
/**
 * removeFromPosition()
 * Retira um conjunto de caracteres de um local específico de uma string
 *
 * @example
 * removeFromPosition('00001119922223445555', 7,9) // -> 000011122223445555
 * removeFromPosition('AAACBBB', 3,4) // -> AAABBB
 *
 * @param {String|Number} value Valor original
 * @param {String|Number} startPosition
 * @param {String|Number} endPosition
 * @returns {String}
 *
 */
function removeFromPosition(value, startPosition, endPosition) {
    return [value.slice(0, startPosition), value.slice(endPosition)].join('');
}
exports.removeFromPosition = removeFromPosition;
/**
 * applyMask()
 * Aplica uma máscara a uma string
 *
 * @example
 * applyMask('59650000', '00.000-000') // -> 59.650-000
 * applyMask('99877665544', '(00) 0 0000-0000') // -> (99) 8 7766-5544
 *
 * @param {String|Number} value Valor original
 * @param {String} mask
 * @returns {String}
 *
 */
function applyMask(value, mask) {
    const maskLen = clearValue(mask).length;
    let masked = clearValue(value, maskLen, { fillZerosAtLeft: true, trimAtRight: true });
    const specialChars = ['/', '-', '.', '(', ')', ' '];
    for (let position = 0; position < mask.length; position += 1) {
        const current = mask[position];
        if (specialChars.includes(current))
            masked = insertAtPosition(masked, current, position);
    }
    return masked;
}
exports.applyMask = applyMask;
/**
 * randomLetter()
 * Pega uma letra maiúscula aleatoriamente
 *
 * @example
 * randomLetter() // -> A
 * randomLetter() // -> S
 *
 * @returns {String}
 */
function randomLetter() {
    const idx = Math.floor(1 + Math.random() * 26);
    return String.fromCharCode(idx + 64);
}
exports.randomLetter = randomLetter;
//# sourceMappingURL=utils.js.map

/***/ }),

/***/ "react":
/*!************************!*\
  !*** external "React" ***!
  \************************/
/***/ ((module) => {

module.exports = window["React"];

/***/ }),

/***/ "@woocommerce/blocks-checkout":
/*!****************************************!*\
  !*** external ["wc","blocksCheckout"] ***!
  \****************************************/
/***/ ((module) => {

module.exports = window["wc"]["blocksCheckout"];

/***/ }),

/***/ "@woocommerce/blocks-registry":
/*!******************************************!*\
  !*** external ["wc","wcBlocksRegistry"] ***!
  \******************************************/
/***/ ((module) => {

module.exports = window["wc"]["wcBlocksRegistry"];

/***/ }),

/***/ "@woocommerce/settings":
/*!************************************!*\
  !*** external ["wc","wcSettings"] ***!
  \************************************/
/***/ ((module) => {

module.exports = window["wc"]["wcSettings"];

/***/ }),

/***/ "@wordpress/element":
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
/***/ ((module) => {

module.exports = window["wp"]["element"];

/***/ }),

/***/ "@wordpress/html-entities":
/*!**************************************!*\
  !*** external ["wp","htmlEntities"] ***!
  \**************************************/
/***/ ((module) => {

module.exports = window["wp"]["htmlEntities"];

/***/ }),

/***/ "@wordpress/i18n":
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
/***/ ((module) => {

module.exports = window["wp"]["i18n"];

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
(() => {
/*!**********************!*\
  !*** ./src/index.js ***!
  \**********************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _woocommerce_blocks_registry__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @woocommerce/blocks-registry */ "@woocommerce/blocks-registry");
/* harmony import */ var _woocommerce_blocks_registry__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_woocommerce_blocks_registry__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _woocommerce_blocks_checkout__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @woocommerce/blocks-checkout */ "@woocommerce/blocks-checkout");
/* harmony import */ var _woocommerce_blocks_checkout__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_woocommerce_blocks_checkout__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _wordpress_html_entities__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @wordpress/html-entities */ "@wordpress/html-entities");
/* harmony import */ var _wordpress_html_entities__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_wordpress_html_entities__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var _woocommerce_settings__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! @woocommerce/settings */ "@woocommerce/settings");
/* harmony import */ var _woocommerce_settings__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(_woocommerce_settings__WEBPACK_IMPORTED_MODULE_6__);
/* harmony import */ var _elements__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./elements */ "./src/elements.js");









// PIX Gateway
const pixSettings = (0,_woocommerce_settings__WEBPACK_IMPORTED_MODULE_6__.getSetting)('paghiper_pix_data', {});
const defaultPixLabel = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('PIX Paghiper', 'paghiper-payments');
const label = (0,_wordpress_html_entities__WEBPACK_IMPORTED_MODULE_5__.decodeEntities)(pixSettings.title) || defaultPixLabel;
const Content = props => {
  const {
    eventRegistration,
    emitResponse
  } = props;
  const {
    onPaymentSetup
  } = eventRegistration;
  const [taxID, setTaxID] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useState)('');
  const [payerName, setPayerName] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useState)('');
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_2__.useEffect)(() => {
    const unsubscribe = onPaymentSetup(async () => {
      // Here we can do any processing we need, and then emit a response.
      // For example, we might validate a custom field, or perform an AJAX request, and then emit a response indicating it is valid or not.

      const paghiperTaxId = taxID;
      const paghiperTaxIdIsValid = !!paghiperTaxId.length;
      const paghiperTaxIdFieldName = "_" + props.gatewayName + "_cpf_cnpj";
      if (paghiperTaxIdIsValid) {
        return {
          type: emitResponse.responseTypes.SUCCESS,
          meta: {
            paymentMethodData: {
              [paghiperTaxIdFieldName]: paghiperTaxId
            }
          }
        };
      }
      return {
        type: emitResponse.responseTypes.ERROR,
        message: 'There was an error'
      };
    });
    // Unsubscribes when this component is unmounted.
    return () => {
      unsubscribe();
    };
  }, [taxID, emitResponse.responseTypes.ERROR, emitResponse.responseTypes.SUCCESS, onPaymentSetup]);
  const onChange = paymentEvent => {
    if (paymentEvent.error) {
      console.log('Paghiper: Payment Error');
    }
    setTaxID(paymentEvent.target.value.replace(/\D/g, ''));
  };
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(react__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(react__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,_wordpress_html_entities__WEBPACK_IMPORTED_MODULE_5__.decodeEntities)(props.gatewayDescription || '')), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_elements__WEBPACK_IMPORTED_MODULE_7__.InlineTaxIdField, {
    gatewayName: props.gatewayName,
    onChange: onChange,
    inputErrorComponent: _woocommerce_blocks_checkout__WEBPACK_IMPORTED_MODULE_4__.ValidationInputError
  }));
};
const Label = props => {
  const {
    PaymentMethodLabel
  } = props.components;
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(PaymentMethodLabel, {
    text: label
  });
};
const PaghiperPix = {
  name: "paghiper_pix",
  label: (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(Label, null),
  content: (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(Content, {
    gatewayName: "paghiper_pix",
    gatewayDescription: pixSettings.description
  }),
  edit: (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(Content, {
    gatewayName: "paghiper_pix",
    gatewayDescription: pixSettings.description
  }),
  canMakePayment: () => true,
  ariaLabel: label,
  paymentMethodId: "paghiper_pix",
  supports: {
    features: pixSettings.supports
  }
};
(0,_woocommerce_blocks_registry__WEBPACK_IMPORTED_MODULE_3__.registerPaymentMethod)(PaghiperPix);

// Billet
const billetSettings = (0,_woocommerce_settings__WEBPACK_IMPORTED_MODULE_6__.getSetting)('paghiper_billet_data', {});
const defaultBilletLabel = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Boleto Paghiper', 'paghiper-payments');
const billetLabel = (0,_wordpress_html_entities__WEBPACK_IMPORTED_MODULE_5__.decodeEntities)(billetSettings.title) || defaultBilletLabel;
const BilletLabel = props => {
  const {
    PaymentMethodLabel
  } = props.components;
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(PaymentMethodLabel, {
    text: billetLabel
  });
};
(0,_woocommerce_blocks_registry__WEBPACK_IMPORTED_MODULE_3__.registerPaymentMethod)({
  name: "paghiper_billet",
  label: (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(BilletLabel, null),
  content: (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(Content, {
    gatewayName: "paghiper_billet",
    gatewayDescription: billetSettings.description
  }),
  edit: (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(Content, {
    gatewayName: "paghiper_billet",
    gatewayDescription: billetSettings.description
  }),
  canMakePayment: () => true,
  ariaLabel: billetLabel,
  supports: {
    features: billetSettings.supports
  }
});
})();

/******/ })()
;
//# sourceMappingURL=index.js.map