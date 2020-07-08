const rules = {
    taobao: {
        translate: {
            'originPrice': '#J_PriceName',
            'promoPrice': '#J_PriceName',
            'size': 'dt:contains("适用年龄"), dt:contains("尺碼"), dt:contains("尺寸"), dt:contains("尺码"), dt:contains("参考身高"), dt:contains("鞋码"), dt:contains("大小描述"), dt:contains("电压")',
            'color': 'dt:contains("顏色"), dt:contains("颜色分类"), dt:contains("颜色"),dt:contains("转速")',
            'amount': 'dt:contains("數量"), dt:contains("数量")',
            'unit': '.tb-amount-widget .mui-amount-unit',
        },
        crawle: {
            'originPrice': '#J_priceStd .tb-rmb-num, #J_StrPrice .tb-rmb-num',
            'promoPrice': '#J_PromoPrice .tb-rmb-num, #J_PromoPriceNum',
            'image': '#J_ThumbView, #J_ImgBooth',
            'shop_nick': '.tb-shop-name a',
            'shop_link': '.tb-shop-name a',
            'amount': '#J_IptAmount',
            'size': 'dt:contains("Kích thước"), dt:contains("Kích thước"), dt:contains("kích thước"), dt:contains("Size"), dt:contains("size")',
            'color': 'dt:contains("Màu sắc"), dt:contains("màu sắc"), dt:contains("màu số"), dt:contains("Color"), dt:contains("color")',
            'lowPrice': 'span[itemprop="lowPrice"]',
            'highPrice': 'span[itemprop="highPrice"]'
        }
    },
    tmall: {
        translate: {
            'originPrice': 'dt:contains("價格"), dt:contains("专柜价"), dt:contains("价格")',
            'promoPrice': 'dt:contains("促銷價"), dt:contains("淘宝价")',
            'size': 'dt:contains("尺碼"), dt:contains("尺寸"), dt:contains("尺码"), dt:contains("套餐類型"), dt:contains("参考身高"), dt:contains("鞋码"), dt:contains("大小描述"), dt:contains("电压")',
            'color': 'dt:contains("顏色"), dt:contains("颜色"), dt:contains("转速")',
            'amount': 'dt:contains("數量"), dt:contains("数量")',
            'unit': '.tb-amount-widget .mui-amount-unit',
        },
        crawle: {
            'originPrice': '#J_DetailMeta > div.tm-clear > div.tb-property > div > div.tm-fcs-panel > dl.tm-tagPrice-panel > dd > span, #J_StrPriceModBox > dd > span',
            'promoPrice': '#J_PromoPrice > dd > div > span, #J_PromoBox > div > span',
            'image': '#J_ThumbView, #J_ImgBooth',
            'shop_nick': '.shopLink',
            'shop_link': '.shopLink',
            'amount': '#J_Amount input',
            'size': 'dt:contains("Kích thước"), dt:contains("kích thước"), dt:contains("Size"), dt:contains("size")',
            'color': 'dt:contains("Màu sắc"), dt:contains("màu sắc"), dt:contains("màu số"), dt:contains("Color"), dt:contains("color")',
            'lowPrice': '#J_PromoPrice .tm-price',
            'highPrice': '#J_PromoPrice .tm-price'
        }
    },
    '1688': {
        translate: {
            'originPrice': 'tr.price > td.price-title',
            'promoPrice': 'tr.price > td.price-title',
            'size': '.d-content .obj-sku .obj-title',
            'color': '.d-content .obj-leading .obj-title',
            'amount': '',
            'unit': '',
        },
        crawle: {
            'originPrice': '.tm-price-panel .tm-price',
            'promoPrice': '.tm-promo-panel .tm-price',
            'image': '.mod-detail-gallery img',
            'shop_nick': '#usermidid',
            'shop_link': '.currentdomain, .enname',
            'amount': '#J_Amount input',
            'size': '',
            'color': 'span.obj-title:contains("Màu sắc"), span.obj-title:contains("màu sắc"), dt:contains("Color"), dt:contains("color")',
            'lowPrice': '#J_PromoPrice .tm-price',
            'highPrice': '#J_PromoPrice .tm-price'
        }
    }
};

var products = [];
var product1688 = [];
var STORAGE = 'tbex_thqc';
var STORAGE_NEW = 'tbex_thqc';
var STORAGE_TOKEN = 'tbex_thqc_token';
var REQUEST_DATA = 'REQUEST_DATA';
var CART_TOKEN = 'CART_TOKEN';
var CLEAR_DATA = 'CLEAR_DATA';
var manifestData = chrome.runtime.getManifest();
var VERSION = manifestData.version;
var HTTP = 'https';
var GDTQ = getHttpsLink(manifestData.homepage_url);
var shortName = manifestData.short_name;
var apiShopUrl = GDTQ + '/api/shop/info';
var apiAddCart = GDTQ + '/api/add/cart';
var ALT = 'THQC';
var TBE_RATE = '.tbe-rate';

// console.log(86,manifestData.input_components);
var config = {
    rate: manifestData.input_components.rate,
    apiShopUrl: apiShopUrl,
    apiAddCart: apiAddCart,
    urlGetRate: GDTQ + '/san-pham/lay-ty-gia',
    // cartUrl: GDTQ + '/order/cart',
    cartUrl: GDTQ + '/don-hang/gio-hang',
    helpUrl: GDTQ + '/danh-muc-bai-viet/chi-tiet-20-huong-dan-tao-don-hang-tren-he-thong',
    reportUrl: GDTQ + '/bao-cao/gio-hang',
    logoUrl: GDTQ + '/images/extention_logo.png',
    logoUrl2: GDTQ + '/images/logo_export.png',
    urlCurrentVersion: GDTQ + '/san-pham/current-tool-version',
    currentVer: chrome.runtime.getManifest().version,
    allowedDomains: ['tmall', 'taobao', '1688'],
    title: ALT
};

// 
/*$('#J_PromoPrice, #J_priceStd, #J_PromoWrap').bind('DOMNodeInserted', function(e) {
    var element = e.target;
    setTimeout(function() {
        //console.log('element',element);
    }, 2000);
});*/
// 
var currentDomain = getCurrentDomain();
/*var defaultProduct = {
    'rate': config.rate,
    'name': '',
    'pro_link' : '',
    'image': '',
    'price': 0,
    'price_arr': '',
    'size': '',
    'sizetxt': '',
    'color': '',
    'colortxt': '',
    'pro_properties': '',
    'amount': 0,
    'beginAmount': 1,
    'shop_nick': '',
    'shop_link': '',
    'domain': currentDomain,
    'site': currentDomain,
    'note': '',
    'count': false,
    'method': 'Chrome Extension'
};*/

var message = 'default';
var btnName = 'Thêm vào giỏ hàng';
var btnNameCart = 'Giỏ hàng';

var React = require('react');
var ReactDOM = require('react-dom');
// var socket = io.connect();

/*
| interface
*/
var cart_container = $('<div id="cart-thqc-parent"></div>');
var notes = $('<div id="pro-thqc-note"></div>');
var warning = $('<div id="cart-thqc-warning"></div>');
var existsElement = $('#J_box_buycart, #J_juValid, .tb-action, .obj-order');
var TextNote = React.createClass({
    displayName: "TextNote",
    render: function () {
        return (
            React.createElement("textarea", {
                className: "thqcNote",
                name: "pro-thqc-note",
                placeholder: "Ghi chú sản phẩm",
                rows: "4",
                cols: "50"
            })
        );
    }
});
var TextWarning = React.createClass({
    displayName: "TextWarning",
    render: function () {
        return (
            React.createElement("span", {className: "text-center success"}, this.props.message)
        );
    }
});
var BtnAddPro = React.createClass({
    displayName: "BtnAddPro",
    cartLink: function () {
        return window.open(config.cartUrl);
    },
    render: function () {
        var path = chrome.extension.getURL('images/128x128.png');
        var img = chrome.extension.getURL('images/32x32.png');
        return (
            React.createElement("div", null,
                React.createElement("a", {
                        className: "btn-tbex btn-tbex-small btn-tbex-default btn-thqc-cart-url",
                        onClick: this.cartLink
                    },
                    React.createElement("img", {src: img}), React.createElement("sup", {className: "pro-thqc-add-new"}, " ")
                    /*<span>{ALT}</span>*/
                ),
                React.createElement("a", {
                    className: "btn-tbex btn-tbex-small btn-tbex-success btn-thqc-add",
                    onClick: addToShopCart
                }, this.props.btnname)
            )
        );
    }
});
/*var BtnAddPro = React.createClass({
  cartLink: function() {
        return window.open(config.cartUrl);
    },
  cartHome: function() {
        return window.open(GDTQ);
    },
  render: function(){
    var path = chrome.extension.getURL('images/144x144.png');
    var img = chrome.extension.getURL('images/cart_1.png');
    return(
      <div className="container-tbex">
        <div className="pull-left">
          <a className='btn-thqc-cart-home' onClick={this.cartHome}>
              <span>{config.title}</span>
          </a>
        </div>
        <div className="pull-left" id="cart-thqc-shop-info"></div>
        <div className="pull-left" id="cart-thqc-warning"></div>
        <div className="pull-right">
          <a className='call-to-area-text btn-thqc-add' onClick={addToShopCart}>{this.props.btnname}</a>
          <a id="btn-thqc-cart-url" className='call-to-area-text btn-thqc-cart-url' onClick={this.cartLink}>
                {this.props.btnnameCart}
            </a>
        </div>
      </div>
    );
  }
});*/

//$('body').append('<div id="react-taobao"></div>');
if (typeof window !== 'undefined') {
    window.React = React;
}
window.addEventListener("message", function (event) {
    if (event.origin != GDTQ) {
        return;
    }

    var emptyData = {};
    switch (event.data.type) {
        case CART_TOKEN:
            var token = event.data.id;
            emptyData[STORAGE_TOKEN] = [token];
            setData(emptyData, function () {
                console.info('set local cart token!');
            });
            event.source.postMessage({error: 0, message: 'set local data _token!'}, event.origin);
            break;
        case REQUEST_DATA:
            getData(STORAGE_NEW, function (items) {
                if (items) {
                    event.source.postMessage({error: 2, message: '', data: items}, event.origin);
                } else {
                    event.source.postMessage({error: 1, message: msg}, event.origin);
                }
            });
            break;
        case CLEAR_DATA:
            emptyData[STORAGE_NEW] = [];
            setData(emptyData, function () {
                console.info('All data was cleared!');
            });
            event.source.postMessage({error: 0, message: 'All data was cleared!'}, event.origin);
            break;
        default:
            break;
    }
}, false);

/*main code*/
$(document).ready(function () {
    // clearData();
    if (checkForShowTool() && checkExpired() && !checkMyDomain()) {
        //clearfix
        //clearOldData();
        // Remove currency label
        delElmentPrice();
        // Translate
        $(rules[currentDomain].translate.originPrice).text('Giá');
        $(rules[currentDomain].translate.promoPrice).text('Giá bán');
        $(rules[currentDomain].translate.size).text('Kích thước');
        $(rules[currentDomain].translate.color).text('Màu sắc');
        $(rules[currentDomain].translate.amount).text('Số lượng');
        $(rules[currentDomain].translate.unit).text('sản phẩm');
        //add element
        /*existsElement.before(notes);
        $('body').append(cart_container);
        ReactDOM.render(<TextNote/>, document.getElementById('pro-thqc-note'));
        ReactDOM.render(<BtnAddPro btnname={btnName} btnnameCart={btnNameCart}/>, document.getElementById('cart-thqc-parent'));
        ReactDOM.render(<TextWarning message={message}/>, document.getElementById('cart-thqc-warning'));
        getShopInfor();*/
        existsElement.before(notes);
        existsElement.before(warning);
        existsElement.before(cart_container);
        ReactDOM.render(React.createElement(TextNote, null), document.getElementById('pro-thqc-note'));
        ReactDOM.render(React.createElement(TextWarning, {message: message}), document.getElementById('cart-thqc-warning'));
        ReactDOM.render(React.createElement(BtnAddPro, {btnname: btnName}), document.getElementById('cart-thqc-parent'));
        getShopInfor();
    }
});
// $(document).ready(function() {
//   getInfoPageShop();
//   getShopInfor();
// });
/*end main*/

/*function*/
function clearData() {
    var emptyData = {};
    emptyData[STORAGE_NEW] = [];
    setData(emptyData, function () {
        console.info('All data was cleared!');
    });
}

function checkExpired() {
    var d = new Date();
    var dateExpired = new Date(2018, 6, 12, 2, 3, 4, 567);
    // var date = d.getDate() + d.getMonth() + getFullYear();
    // console.log(d.getTime(), dateExpired.getTime());
    if (d.getTime() <= dateExpired.getTime()) {
        return true;
    } else {
        return false;
    }
}

function clearOldData() {
    var emptyData = {};
    emptyData[STORAGE] = [];
    setData(emptyData, function () {
        //console.info('All old data was cleared!');
    });
}

function getData(key, cb) {
    chrome.storage.local.get(key, cb);
}

function setData(data, cb) {
    chrome.storage.local.set(data, cb);
}

function getCurrentDomain() {
    var hostName = window.location.hostname;
    if (typeof hostName === 'undefined') {
        return false;
    }
    hostName = hostName.toLowerCase();
    for (var i = config.allowedDomains.length - 1; i >= 0; i--) {
        if (hostName.indexOf(config.allowedDomains[i]) > 0) {
            return config.allowedDomains[i];
        }
    }

}

//jQuery Fly to cart effect
function flyToCart() {
    //var cart = $('#btn-thqc-cart-url');
    var cart = $('#cart-thqc-parent');
    var imgtodrag = $(rules[currentDomain].crawle.image).eq(0);
    if (imgtodrag) {
        var imgclone = imgtodrag.clone()
            .offset({
                top: imgtodrag.offset().top,
                left: imgtodrag.offset().left
            })
            .css({
                'opacity': '0.5',
                'position': 'absolute',
                'height': '150px',
                'width': '150px',
                'z-index': '100000000'
            })
            .appendTo($('body'))
            .animate({
                'top': cart.offset().top + 10,
                'left': cart.offset().left + 10,
                'width': 75,
                'height': 75
            }, 2000, 'easeInOutExpo');

        warningMeasage('Thông báo', '<p>Đã thêm sản phẩm vào giỏ hàng</p>', 'success');

        imgclone.animate({
            'width': 0,
            'height': 0
        }, function () {
            if ($(this).detach()) {

            }
        });
    }
}

function $_GET(param) {
    var vars = {};
    window.location.href.replace(
        /[?&]+([^=&]+)=?([^&]*)?/gi, // regexp
        function (m, key, value) { // callback
            vars[key] = value !== undefined ? value : '';
        }
    );

    if (param) {
        return vars[param] ? vars[param] : null;
    }
    return vars;
}

function getlocalData(key) {
    if (typeof(Storage) !== "undefined") {
        // console.log("getlocalData");
        return localStorage.getItem(key);
    } else {
        console.log('Sorry! No Web Storage support..');
        return false;
    }
}

function setlocalData(key, data) {
    if (typeof(Storage) !== "undefined") {
        // console.log("setlocalData");
        localStorage.setItem(key, data);
        return true;
    } else {
        console.log('Sorry! No Web Storage support..');
        return false;
    }
};

function removelocalData(key) {
    if (typeof(Storage) !== "undefined") {
        // console.log("removelocalData");
        localStorage.removeItem(key);
        return true;
    } else {
        console.log('Sorry! No Web Storage support..');
        return false;
    }
};

function changeLink(link) {
    if (typeof link !== 'undefined' && link !== null && link.length) {
        link = link.replace('http:', '');
        link = link.replace('https:', '');
        link = link.replace('//', '');
        link = 'https://' + link;
        return link;
    } else {
        return false;
    }
}

function getHttpsLink(link) {
    link = link.replace('https://', 'http://');
    link = link.replace('http://', 'https://');
    return link;
}

function fixPriceForGoogleTranslate(input_price) {
    if (input_price.indexOf(',') > -1) {
        input_price = input_price.replace('.', '');
        input_price = input_price.replace(',', '.');
    }
    return parseFloat(input_price);
}

function delOldExtension() {
    $('#tbe-menubar, #tbe-info, #tbe-notes, #tbe-warning-bar').remove();
}

function delElmentPrice() {
    //delOldExtension();
    // Remove currency label
    $('.tb-rmb, .tm-yen', '.fd-cny').remove();
    $('#J_PromoPrice, #J_priceStd, #J_PromoWrap').on('DOMNodeInserted', function (ev) {
        $('.tb-rmb, .tm-yen, .fd-cny').remove();
    });
}

function checkForShowTool() {
    var check = true;
    var warn = $('.warning-info .sea-iconfont.warn-icon').length;
    if (warn > 0) {
        check = false;
    }
    if ((window.location.pathname).indexOf('item') === -1 && window.location.host != 'detail.1688.com') {
        check = false;
    }
    //console.log(check);
    return check;
}

function scrollToAlert() {
    return false;
    /*if($('#J_isSku, #J_isku, .d-content .obj-sku, .tb-sku > .tb-prop').length && $('#J_SKU, .mod-detail-purchasing, #J_DetailMeta .tb-sku, #J_isku>.tb-skin>.tb-prop').length){
          $('#J_isSku, #J_isku, #J_DetailMeta .tb-sku, .d-content .obj-sku').addClass('tbe-bd-width tbe-bd-warning');
          $('html, body').animate({
               // scrollTop: $('#J_isSku, #J_isku, #J_DetailMeta .tb-sku, .mod-detail-purchasing').offset().top
               scrollTop: $('#detail, #J_isku, #mod-detail').offset().top

           }, 2000);
       }*/
}

function warningMeasage(type, msg, style) {
    var myTimeOut;
    $('#cart-thqc-warning').removeClass('alert-success');
    $('#cart-thqc-warning').removeClass('alert-warning');
    $('#cart-thqc-warning').addClass('alert-' + style);
    $('#cart-thqc-warning').html(msg);
    $('#cart-thqc-warning').show();
    myTimeOut = setTimeout(function () {
        $('#cart-thqc-warning').hide();
    }, 3000);
    $('#cart-thqc-warning').mouseout(function () {
        myTimeOut = setTimeout(function () {
            $('#cart-thqc-warning').hide();
        }, 3000);
    });

    $('#cart-thqc-warning').mouseover(function () {
        clearTimeout(myTimeOut);
    });

}

function taobaoSkuId(size, color) {
    var skuid = ';' + size + ';' + color + ';';
    var unskuid = ';' + color + ';' + size + ';';
    var tmp_pro = JSON.parse(getlocalData('tmp_g_config'));
    if (typeof tmp_pro === 'object' && tmp_pro !== null) {
        if (typeof tmp_pro.skuInfo !== 'undefined' && typeof tmp_pro.skuInfo.skuMap !== 'undefined') {
            tmp_pro = tmp_pro.skuInfo.skuMap;
            if (typeof tmp_pro === 'object') {
                $.each(tmp_pro, function (key, value) {
                    // console.log(key, value);
                    if (key == skuid) {
                        skuid = value.skuId;
                    } else if (key == unskuid) {
                        skuid = value.skuId;
                    }
                });
            } else {
                skuid = null;
            }
        } else {
            skuid = null;
        }
    }

    return skuid;
}

function taobaoShopNick() {
    var nick = '';
    if (!nick.length) {
        var shop_nick = $('.tb-shop-name a, .shop-name a.shop-name-link');
        if (typeof shop_nick !== 'undefined') {
            nick = $(shop_nick).attr('title');
        }
        if (typeof nick === 'undefined' || !nick.length) {
            nick = $(shop_nick).text();
            nick = getShopName(nick);
        }
    }
    if (!nick.length) {
        var tmp_pro = JSON.parse(getlocalData('tmp_g_config'));
        if (typeof tmp_pro === 'object' && tmp_pro !== null) {
            if (typeof tmp_pro.fav !== 'undefined' && typeof tmp_pro.fav.shopTitle !== 'undefined') {
                nick = decodeURIComponent(tmp_pro.fav.shopTitle);
            }
        }
    }
    return nick;
}

function getShopName(str) {
    str = str.replace(/(?:\r\n|\r|\n)/g, ' ');
    str = str.trim();
    return str;
}

function taobaoShopLink() {
    var link = '';
    var shop_link = $('.tb-shop-name a, .shop-name a.shop-name-link');
    if (typeof shop_link !== 'undefined') {
        link = $(shop_link).attr('href');
    }
    link = changeLink(link);
    return link;
}

function tmallShopNick() {
    var nick = '';
    // if(!nick.length){
    var shop_nick = $('.shopLink');
    if (typeof shop_nick !== 'undefined') {
        nick = $(shop_nick).text();
    }
    // }
    if (!nick.length) {
        var tmp_pro = JSON.parse(getlocalData('tmp_g_config'));
        if (typeof tmp_pro === 'object' && tmp_pro !== null) {
            if (typeof tmp_pro.sellerNickName !== 'undefined') {
                nick = decodeURIComponent(tmp_pro.sellerNickName);
            }
        }
    }
    return nick;
}

function tmallShopLink() {
    var link = '';
    var shop_link = $('.shopLink');
    if (typeof shop_link === 'undefined' || !shop_link.length) {
        shop_link = $('.hd-shop-name a, .slogo-shopname');
    }
    if (typeof shop_link !== 'undefined' && shop_link.length) {
        link = $(shop_link).attr('href');
    }
    link = changeLink(link);
    return link;
}

function com1688ShopNick() {
    // nick = $('#usermidid').text();
    // if(!nick.length){
    var nick_shop = '';
    var nick = $('.app-common_supplierInfoSmall, .app-import_supplierInfoSmall, .app-smt_supplierInfoSmall, .app-offerdetail_topbar, .app-yuan_supplierInfoSmall');
    if (typeof nick !== 'undefined' && nick.length) {
        nick = nick.attr('data-view-config');
        if (nick.length) {
            nick = JSON.parse(nick);
        }
        if (typeof nick === 'object' && typeof nick.loginId !== 'undefined') {
            nick_shop = nick.loginId;
        }
    }
    if (nick_shop !== 'undefined' && nick_shop.length === 0) {
        nick_shop = $("meta[property='og:product:nick']").attr('content');
        if (nick_shop.length) {
            var start = 0;
            var end = nick_shop.indexOf('url=');
            if (end > 0) {
                nick_shop = nick_shop.slice(start, end);
            }
            nick_shop = nick_shop.replace('name=', '');
            nick_shop = nick_shop.replace(';', '');
            nick_shop = nick_shop.trim();
        }
    }
    // }
    return nick_shop;
}

function com1688ShopLink() {
    var link_shop = '';
    var link = $('.app-offerdetail_topbar, .app-import_topbar, .app-smt_topbar, .app-offerdetail_topbar, .app-common_topbar');
    if (typeof link !== 'undefined' && 'undefined' !== link && link.length) {
        link = $(link).attr('data-view-config');
        if (link.length) {
            link = JSON.parse(link);
            if (typeof link === 'object' && typeof link.currentDomainUrl !== 'undefined') {
                link_shop = link.currentDomainUrl;
            }
        } else {
            link_shop = $('input.currentdomain').val();
        }
    }
    if (!('undefined' !== link_shop && link_shop !== null && link_shop.length)) {
        link = $('meta[property="og:product:nick"]');
        if ('undefined' !== link && link !== null && link.length) {
            link = link.attr('content');
            var n = link.indexOf("url=");
            link = link.slice(n + 4, -1);
            link_shop = link.trim();
        }
    }
    link_shop = changeLink(link_shop);
    return link_shop;
}

function getSingleProduct1688Com() {
    var newProduct = {
        img: '',
        list: []
    };
    var price = $('.price td .value:first, .price-now');
    if (typeof price !== 'undefined') {
        price = parseFloat(price.text());
    } else {
        price = 0;
    }
    var qty = $('.unit-detail-amount-control .amount-input');
    if (typeof qty !== 'undefined') {
        qty = parseInt(qty.val());
    } else {
        qty = 0;
    }
    var structure = {
        color: "",
        isMix: "false",
        max: "",
        min: "0",
        mixAmount: "0",
        mixBegin: "0",
        mixNumber: "0",
        price: price,
        qty: qty,
        size: "",
        skuName: "",
        wsRuleNum: "",
        wsRuleUnit: ""
    };
    newProduct.list.push(structure);
    var img = $('.mod-detail-gallery .tab-pane .box-img img');
    if (typeof img !== 'undefined') {
        img = img.attr('src');
    }
    if (typeof img !== 'undefined' && img.length) {
        newProduct.img = img;
    }
    return newProduct;
}

function getProduct1688() {
    var result = [];
    var price, size, qty;
    var list = [];
    var img1688 = $('.tab-pane img').attr('src');
    var color1688 = $('.list-leading .active .selected').attr('title');
    if (typeof color1688 === 'undefined') {
        color1688 = '';
    }
    if (typeof img1688 !== 'undefined') {
        img1688 = changeLink(img1688);
    }
    var tmp, item;
    var el_data = $('.table-sku tr');
    if (el_data.length) {
        for (var i = 0; i < el_data.length; i++) {
            item = el_data[i];
            var image = '';
            var imgOwn = $(item).find('.name .image').data('imgs');
            if (typeof imgOwn === 'object' && typeof imgOwn !== 'undefined') {
                image = changeLink(imgOwn.preview);
            }
            // console.log(item);
            tmp = $(item).data('sku-config');
            // console.log(tmp);
            // tmp = JSON.parse(tmp);
            qty = $(item).find('.amount-input').val();
            qty = parseInt(qty);
            if (typeof qty !== 'undefined' && qty > 0) {
                price = $(item).find('.price .value').text();
                size = $(item).find('.name span').text();
                tmp.qty = qty;
                tmp.price = parseFloat(price);
                tmp.color = color1688;
                tmp.image = image;
                list.push(tmp);
            }
        }
    }
    // console.log(list);

    var structure = {
        color: color1688,
        img: img1688,
        list: list
    };
    result.push(structure);
    return result;
}

/**
 * Lấy gía
 * @return array
 */
function getPrice() {
    if (currentDomain == '1688') {
        return getPriceFrom1688();
    }

    return getPriceFromTaobao();
}

/**
 * Lấy giá từ 1688
 * @return array
 */
function getPriceFrom1688() {
    var priceOf1688 = [];
    $('tr.price td[data-range]').each(function (ind, el) {
        var rangePrice = $(el).attr('data-range');
        if (typeof rangePrice == 'undefined') {
            priceOf1688 = parseFloat($(el).find('.value').text());
        } else {
            rangePrice = $.parseJSON(rangePrice);
            priceOf1688.push(rangePrice);
        }
    });

    // Try once get price
    if (typeof priceOf1688 == 'object' && priceOf1688.length == 0) {
        priceOf1688 = parseFloat($('#mod-detail-price').find('span.num').text());
    }

    if (isNaN(priceOf1688) && typeof priceOf1688 !== 'object') {
        priceOf1688 = $('.table-sku').find('tr:first').find('td.price').find('em.value').text();
        priceOf1688 = parseFloat(priceOf1688);
    }
    // console.log('getPriceFrom1688',priceOf1688);
    return priceOf1688;
}

/**
 * Lấy giá từ taobao và tmall
 * @return array
 */
function getPriceFromTaobao() {
    var priceWrap = $(rules[currentDomain].crawle.originPrice),
        promoWrap = $(rules[currentDomain].crawle.promoPrice),
        priceText = priceWrap.text(),
        promoText = promoWrap.text(),
        lowPrice = 0,
        highPrice = 0,
        lowPromo = 0,
        highPromo = 0;
    var priceRange;
    if (priceText.indexOf('-') > -1) {
        priceRange = priceText.split('-');
        lowPrice = parseFloat(priceRange[0]);
        highPrice = parseFloat(priceRange[1]);
        priceText = 0;
    } else {
        priceText = parseFloat(priceText);
    }

    if (promoText.indexOf('-') > -1) {
        priceRange = promoText.split('-');
        lowPromo = parseFloat(priceRange[0]);
        highPromo = parseFloat(priceRange[1]);
        promoText = 0;
    } else {
        promoText = parseFloat(promoText);
    }

    return {
        'orgPrice': priceText,
        'proPrice': promoText,
        'lowPrice': lowPrice,
        'highPrice': highPrice,
        'lowPromo': lowPromo,
        'highPromo': highPromo
    };
}

function addOrRemoveCartItem(action, data) {
    getData(STORAGE_NEW, function (item) {
        products = item.tbex_thqc || [];
        switch (action) {
            case 'add':
                if ($.isArray(data)) {
                    for (var i = data.length - 1; i >= 0; i--) {
                        products.push(data[i]);
                    }
                } else {
                    products.push(data);
                }
                break;
            case 'remove':
                products.removeAt(data);
                break;
        }

        var newData = {};
        newData[STORAGE_NEW] = products;
        setData(newData, function () {
            addProductToCart();
            flyToCart();
            // console.log('newData',newData);
            console.log('[thqc] Products saved!');
        });
    });
}

function checkForTaobao() {
    //taobao.com, tmall.com
    var element = $("#J_SKU .J_SKU, .tb-sku .J_TSaleProp, #J_isku .tb-skin .tb-prop");//taobao.com
    if (element.length == 0) {
        return true;
    }
    var countproperty = 0;
    countproperty = $("#J_SKU dl, .tb-sku .tm-sale-prop, #J_isku .tb-skin .tb-prop").length;
    var countselected = 0;
    countselected = $("#J_SKU .J_SKU.tb-selected, .tb-sku .J_TSaleProp .tb-selected, #J_isku .tb-skin .tb-prop .J_TSaleProp .tb-selected").length;
    if (countselected == 0) {
        warningMeasage('Thông báo', 'Bạn chưa chọn thuộc tính cho sản phẩm.', 'warning');
        scrollToAlert();
        return false;
    }
    //
    if (countselected > 0 && countproperty > countselected) {
        warningMeasage('Thông báo', 'Bạn phải chọn đầy đủ thuộc tính cho sản phẩm.', 'warning');
        scrollToAlert();
        return false;
    } else if (countselected > 0 && countproperty == countselected) {
        return true;
    }

    return false;
}

function checkFor1688Com() {
    var countselected = 0;
    var minCount = 1;
    var beginAmount = 1;
    minCount = $('.de-cal-content .amount-input').val();
    // console.log('minCount',minCount);
    var data_unit = '';
    data_unit = $('.unit-detail-freight-cost').attr("data-unit-config");
    if (typeof data_unit !== 'undefined') {
        data_unit = JSON.parse(data_unit);
    }
    if (typeof data_unit === "object") {
        beginAmount = parseInt(data_unit.beginAmount);
    }
    if (minCount < beginAmount) {
        warningMeasage('Thông báo', 'Shop yêu cầu mua tối thiểu ' + beginAmount + ' sản phẩm.', 'warning');
        scrollToAlert();
        return false;
    }
    var amount = 0;
    countselected = $(".list-leading .unit-detail-spec-operator").length;
    if (countselected > 0) {
        countselected = 0;
        countselected = $(".list-leading .selected").length;
        if (countselected == 0) {
            warningMeasage('Thông báo', 'Bạn chưa chọn thuộc tính cho sản phẩm.', 'warning');
            scrollToAlert();
            return false;
        }
    }
    var totalAmount = 0;
    var amount1688Com = $('.amount .amount-input, .obj-amount .amount-input');
    if (typeof amount1688Com !== 'undefined' && amount1688Com.length) {
        amount1688Com.each(function (ind, el) {
            totalAmount += el.value;
        });
    } else {
        amount1688Com = $('.area-panel .unit-detail-amount-control .amount-input');
        if (typeof amount1688Com !== 'undefined') {
            totalAmount = amount1688Com.val();
        }
    }

    if (totalAmount == 0) {
        warningMeasage('Thông báo', 'Bạn chưa chọn số lượng sản phẩm cần mua.', 'warning');
        scrollToAlert();
        return false;
    } else {
        return true;
    }
}

function addToShopCart() {
    delElmentPrice();
    //clearOldData();
    if (currentDomain == '1688') {
        return addToCartOf1688();
    }
    if (!checkForTaobao()) {
        return false;
    }
    return adToCartTaobao();
}

function adToCartTaobao() {
    var shopNick = '';
    var shopUrl = '';
    var pro_name = '';
    if (currentDomain === 'taobao') {
        pro_name = $('#J_ThumbView, #J_ImgBooth').attr('alt');
        if (typeof pro_name === 'undefined') {
            pro_name = $('#J_Title .tb-main-title').data('title');
        }
        shopNick = taobaoShopNick();
        shopUrl = taobaoShopLink();
    } else if (currentDomain === 'tmall') {
        pro_name = $('#J_ThumbView, #J_ImgBooth').attr('alt');
        shopNick = tmallShopNick();
        shopUrl = tmallShopLink();
    }
    var imageTaoBao = $(rules[currentDomain].crawle.image).attr('src');
    var size = $(rules[currentDomain].crawle.size).next().find('.tb-selected').data('pv'),
        color = $(rules[currentDomain].crawle.color).next().find('.tb-selected').data('pv');
    if (typeof size === "undefined") {
        size = $(rules[currentDomain].crawle.size).next().find('.tb-selected').data('value');
    }
    if (typeof size === "undefined") {
        size = '';
    }
    if (typeof color === 'undefined') {
        color = $(rules[currentDomain].crawle.color).next().find('.tb-selected').data('value');
    }
    if (typeof color === "undefined") {
        color = '';
    }
    var sizetxt = $(rules[currentDomain].crawle.size).next().find('.tb-selected a').text(),
        colortxt = $(rules[currentDomain].crawle.color).next().find('.tb-selected a').text();

    var priceValue = $(rules[currentDomain].crawle.originPrice).text(),
        promoValue = $(rules[currentDomain].crawle.promoPrice).text();
    priceValue = fixPriceForGoogleTranslate(priceValue);
    promoValue = fixPriceForGoogleTranslate(promoValue);
    var price = priceValue;
    if (promoValue && !isNaN(promoValue)) {
        price = promoValue;
    }
    var pro_properties = '';
    if (currentDomain == 'taobao') {
        pro_properties = taobaoSkuId(size, color);
    } else if (currentDomain == 'tmall') {
        pro_properties = $_GET('skuId');
    }
    var defaultProduct = {
        'rate': config.rate,
        'name': pro_name,
        'pro_link': changeLink(window.location.href),
        'image': changeLink(imageTaoBao),
        'price': price,
        'price_arr': '',
        'size': size,
        'sizetxt': sizetxt,
        'color': color,
        'colortxt': colortxt,
        'pro_properties': pro_properties,
        'amount': parseInt($(rules[currentDomain].crawle.amount).val()),
        'beginAmount': 1,
        'shop_nick': shopNick,
        'shop_link': changeLink(shopUrl),
        'domain': currentDomain,
        'site': currentDomain,
        'note': $('#pro-thqc-note .thqcNote').val(),
        'count': false,
        'method': 'Chrome Extension'
    };

    addOrRemoveCartItem('add', defaultProduct);
}

function addToCartOf1688() {
    if (!checkFor1688Com()) {
        return false;
    }

    var productList1688 = [];
    var shopNick = '';
    var shopUrl = '';
    var pro_name = '';
    pro_name = $('.mod-detail-gallery img').attr('alt');
    shopNick = com1688ShopNick();
    shopUrl = com1688ShopLink();

    var beginAmount = 1;
    var unit_config = $('.unit-detail-freight-cost').attr("data-unit-config");
    if (typeof unit_config !== 'undefined') {
        unit_config = JSON.parse(unit_config);
    }
    if (typeof unit_config === 'object') {
        beginAmount = parseInt(unit_config.beginAmount);
    }
    product1688 = getProduct1688();
    if (product1688[0].list.length == 0) {
        product1688 = [];
        product1688.push(getSingleProduct1688Com());
    }
    product1688.forEach(function (prd) {
        var _ids = prd.img.split('/');
        _ids = _ids[_ids.length - 1];
        _ids = _ids.split('.')[0];
        _ids = _ids.split('_');
        prd.list.forEach(function (item) {
            var linkImage = prd.img;
            if (typeof item.image !== 'undefined' && item.image) {
                linkImage = item.image;
            }
            linkImage = changeLink(linkImage);
            var defaultProduct = {
                'proId': parseInt(_ids[1], 10),
                'skullId': parseInt(_ids[0], 10),
                'rate': config.rate,
                'pro_link': changeLink(window.location.href),
                'image': linkImage,
                'name': pro_name,
                'price': item.price,
                'price_arr': getPrice(),
                'size': item.skuName,
                'sizetxt': item.skuName,
                'color': item.color,
                'colortxt': item.color,
                'amount': item.qty,
                'beginAmount': beginAmount,
                'shop_nick': shopNick,
                'shop_link': changeLink(shopUrl),
                'site': currentDomain,
                'domain': currentDomain,
                'note': $('#pro-thqc-note .thqcNote').val(),
                'count': false,
                'method': 'Chrome Extension'
            };
            productList1688.push(defaultProduct);
        });
    });

    addOrRemoveCartItem('add', productList1688);
}

function checkMyDomain() {
    var hostName = window.location.hostname;
    hostName = hostName.toLowerCase();
    if (hostName == shortName) {
        return true;
    }
    return false;
}

function addProductToCart() {
    getData(STORAGE_TOKEN, function (token) {
        if (typeof token.tbex_thqc_token === 'undefined' || token.tbex_thqc_token === null) {
            // console.log(token);
            return;
        }
        getData(STORAGE_NEW, function (item) {
            var id = token.tbex_thqc_token[0];
            var dataCart = item.tbex_thqc;
            dataCart = JSON.stringify(dataCart);
            // console.log(id);
            // console.log(dataCart);
            $.ajax({
                method: "POST",
                url: config.apiAddCart,
                data: {_token: id, cart: dataCart}
            })
                .done(function (response, textStatus, jqXHR) {
                    // console.log(response.error);
                    if (response.error == 0) {
                        clearData();
                    }
                    // response.data.rating = 10;
                    // response.data.complain = 20;
                    // response.data.in_cn = 10;
                    // response.data.transaction = 10;
                });
        });
    });
}

function getShopInfor() {
    let shopNick = '';
    let shopUrl = '';
    if (currentDomain === '1688') {
        shopNick = com1688ShopNick();
        shopUrl = com1688ShopLink();
    }
    if (currentDomain === 'taobao') {
        shopNick = taobaoShopNick();
        shopUrl = taobaoShopLink();
    }
    if (currentDomain === 'tmall') {
        shopNick = tmallShopNick();
        shopUrl = tmallShopLink();
    }
    $.ajax({
        method: "POST",
        url: config.apiShopUrl,
        data: {name: shopNick, url: shopUrl}
    })
        .done(function (response, textStatus, jqXHR) {
            // response.data.rating = 10;
            // response.data.complain = 20;
            // response.data.in_cn = 10;
            // response.data.transaction = 10;
            var rating = '';
            if (response.data.rate) {
                var rate = response.data.rate;
                config.rate = rate;
                rate = (rate).format();
                $(TBE_RATE).text(rate);
            }
            if (response.data.rating) {
                rating += 'Độ tin cậy: <span class="tong-quan">' + response.data.rating + '</span>';
            }
            var khieuNai = '';
            if (response.data.complain) {
                khieuNai += 'Tỷ lệ khiếu nại: <span class="khieu-nai">' + response.data.complain + '</span>';
            }
            var khoCn = '';
            if (response.data.in_cn) {
                khoCn += 'Thời gian Shop phát hàng (ngày): <span class="kho-cn">' + response.data.in_cn + '</span>';
            }
            var transaction = '';
            // if(response.data.transaction) {
            //   transaction += 'Giao dịch thành công: <span class="kho-cn">'+response.data.transaction+'</span>';
            // }
            var html = rating + khieuNai + khoCn + transaction;
            if (response.data.od_success === 'undefined' || !(response.data.od_success)) {
                html = 'Độ tin cậy: <span class="tong-quan">Chưa có đánh giá</span>';
            }
            $('#cart-thqc-shop-info').append(html);
            getInfoPageShop();
        });
}

function getInfoPageShop() {
    if (checkMyDomain()) {
        return false;
    }
    var tbe_price = '';
    var tbe_inventory = '';
    var objPrice = getPrice();
    // console.log(objPrice);
    var productPrice = '';
    if (currentDomain == '1688') {
        tbe_inventory = $(".mod-detail-purchasing").attr('data-mod-config');
        if (tbe_inventory.length > 0) {
            tbe_inventory = JSON.parse(tbe_inventory);
        }
        if (typeof tbe_inventory === "object") {
            tbe_inventory = parseInt(tbe_inventory.max);
        }
        if (typeof tbe_inventory == 'number') {
            tbe_price += '<dl><dd style="width:100%"><span class="text-danger">Shop hiện còn <b>' + tbe_inventory + '</b> sản phẩm</span></dd></dl>';
        }
        if (typeof objPrice == 'number') {
            productPrice = (Math.round(objPrice * config.rate)).format();
        } else {
            if (objPrice.length > 0) {
                if (parseInt(objPrice[0].begin) > 1) {
                    tbe_price += '<dl><dd style="width:100%"><span class="text-danger">Shop yêu cầu mua tối thiểu ' + objPrice[0].begin + ' sản phẩm</span></dd></dl>';
                }
                for (var i = 0; i <= objPrice.length - 1; i++) {
                    if (objPrice[i].end.length > 0) {
                        tbe_price += '<dl><dd>Mua: ' + objPrice[i].begin + ' - ' + objPrice[i].end + ' sản phẩm</dd><dd>Giá: <span class="tbe-color-price">¥' + objPrice[i].price + '</span></dd></dl>';
                    } else {
                        tbe_price += '<dl><dd>Mua: &gt;' + objPrice[i].begin + ' sản phẩm</dd><dd>Giá: <span class="tbe-color-price">¥' + objPrice[i].price + '</span></dd></dl>';
                    }
                }
            }
            productPrice = (Math.round(objPrice[0].price * config.rate)).format();
        }
    } else {
        tbe_inventory = $('#J_EmStock, #J_SpanStock').text();
        if (tbe_inventory.length > 0) {
            tbe_inventory = retnum(tbe_inventory);
            tbe_inventory = parseInt(tbe_inventory);
        }
        if (typeof tbe_inventory == 'number') {
            if (currentDomain == 'TMALL') {
                tbe_price += '<dl><dd style="width:100%"><span class="text-danger">Shop hiện còn <b>' + tbe_inventory + '</b> sản phẩm</span></dd></dl>';
            } else {
                tbe_price += '<dl><dd style="width:100%"><span class="text-danger">Shop giới hạn mua tối đa <b class="tbe-color-price">' + tbe_inventory + '</b> sản phẩm</span></dd></dl>';
            }
        }
        productPrice = (Math.round(objPrice.orgPrice * config.rate)).format();
        // Tìm giá bán của sản phẩm
        if ((objPrice.orgPrice > 0 && objPrice.orgPrice > objPrice.proPrice) || ((objPrice.orgPrice == 0 || isNaN(objPrice.orgPrice)) && objPrice.proPrice > 0)) {
            productPrice = (Math.round(objPrice.proPrice * config.rate)).format();
        } else {
            if (objPrice.lowPrice > 0 && objPrice.highPrice > 0) {
                productPrice = (Math.round(objPrice.lowPrice * config.rate)).format() + ' - ' + (Math.round(objPrice.highPrice * config.rate)).format();
            }
            if (objPrice.lowPromo > 0 && objPrice.highPromo > 0) {
                productPrice = (Math.round(objPrice.lowPromo * config.rate)).format() + ' - ' + (Math.round(objPrice.highPromo * config.rate)).format();
            }
        }
    }
    // Append info
    if (tbe_price.length > 0) {
        tbe_price = '<div class="bg-info">' + tbe_price + '</div>';
    }
    var tbeInfo = [
        '<div id="tbe-info-shop">',
        '<img style="width:70px;" src="' + chrome.extension.getURL('images/144x144.png') + '" alt="' + ALT + '" />',
        '<ul>',
        '<li>Giá bán: <b class="tbe-color-price">' + productPrice + '</b> đ</li>',
        '<li>Tỷ giá: <span class="tbe-rate tbe-color-price">' + (config.rate).format() + '</span> đ/tệ</li>',
        '</ul>',
        tbe_price,
        '<div class="tbe-info-warning">(!!) Vui lòng chọn đầy đủ thông tin sản phẩm ở bên dưới để xem giá chuẩn.</div>',
        '</div>'
    ].join('');
    $('#J_Title, .tb-detail-hd, #mod-detail-price').append(tbeInfo);
}

function retnum(str) {
    var num = str.replace(/[^0-9]/g, '');
    return num;
}