const niniex = (e, t, n) => {
    "use strict";

    function i() {
        var e = {};
        e[B] = [], r(e, function () {
            console.info("All data was cleared!")
        })
    }

    function o(e, t) {
        chrome.storage.local.get(e, t)
    }

    function r(e, t) {
        chrome.storage.local.set(e, t)
    }

    function s() {
        var e = window.location.hostname;
        if ("undefined" == typeof e) return !1;
        e = e.toLowerCase();
        for (var t = ie.allowedDomains.length - 1; t >= 0; t--) if (e.indexOf(ie.allowedDomains[t]) > 0) return ie.allowedDomains[t]
    }

    function a() {
        var e = $("#cart-thqc-parent"), t = $(W[oe].crawle.image).eq(0);
        if (t) {
            var n = t.clone().offset({top: t.offset().top, left: t.offset().left}).css({
                opacity: "0.5",
                position: "absolute",
                height: "150px",
                width: "150px",
                "z-index": "100000000"
            }).appendTo($("body")).animate({
                top: e.offset().top + 10,
                left: e.offset().left + 10,
                width: 75,
                height: 75
            }, 2e3, "easeInOutExpo");
            g("Thông báo", "<p>Đã thêm sản phẩm vào giỏ hàng</p>", "success"), n.animate({
                width: 0,
                height: 0
            }, function () {
                $(this).detach()
            })
        }
    }

    function l(e) {
        var t = {};
        return window.location.href.replace(/[?&]+([^=&]+)=?([^&]*)?/gi, function (e, n, i) {
            t[n] = void 0 !== i ? i : ""
        }), e ? t[e] ? t[e] : null : t
    }

    function u(e) {
        return "undefined" != typeof Storage ? localStorage.getItem(e) : (console.log("Sorry! No Web Storage support.."), !1)
    }

    function c(e) {
        return !("undefined" == typeof e || null === e || !e.length) && (e = e.replace("http:", ""), e = e.replace("https:", ""), e = e.replace("//", ""), e = "https://" + e)
    }

    function h(e) {
        // return e = e.replace("https://", "http://"), e = e.replace("http://", "https://")
        return e;
    }

    function p(e) {
        return e
    }

    function d() {
        $(".tb-rmb, .tm-yen", ".fd-cny").remove(), $("#J_PromoPrice, #J_priceStd, #J_PromoWrap").on("DOMNodeInserted", function (e) {
            $(".tb-rmb, .tm-yen, .fd-cny").remove()
        })
    }

    function f() {
        var e = !0, t = $(".warning-info .sea-iconfont.warn-icon").length;
        return t > 0 && (e = !1), window.location.pathname.indexOf("item") === -1 && "detail.1688.com" != window.location.host && (e = !1), e
    }

    function m() {
        return !1
    }

    function g(e, t, n) {
        var i;
        $("#cart-thqc-warning").removeClass("alert-success"), $("#cart-thqc-warning").removeClass("alert-warning"), $("#cart-thqc-warning").addClass("alert-" + n), $("#cart-thqc-warning").html(t), $("#cart-thqc-warning").show(), i = setTimeout(function () {
            $("#cart-thqc-warning").hide()
        }, 3e3), $("#cart-thqc-warning").mouseout(function () {
            i = setTimeout(function () {
                $("#cart-thqc-warning").hide()
            }, 3e3)
        }), $("#cart-thqc-warning").mouseover(function () {
            clearTimeout(i)
        })
    }

    function v(e, t) {
        var n = ";" + e + ";" + t + ";", i = ";" + t + ";" + e + ";", o = JSON.parse(u("tmp_g_config"));
        return "object" == typeof o && null !== o && ("undefined" != typeof o.skuInfo && "undefined" != typeof o.skuInfo.skuMap ? (o = o.skuInfo.skuMap, "object" == typeof o ? $.each(o, function (e, t) {
            e == n ? n = t.skuId : e == i && (n = t.skuId)
        }) : n = null) : n = null), n
    }

    function y() {
        var e = "";
        if (!e.length) {
            var t = $(".tb-shop-name a, .shop-name a.shop-name-link");
            "undefined" != typeof t && (e = $(t).attr("title")), "undefined" != typeof e && e.length || (e = $(t).text(), e = b(e))
        }
        if (!e.length) {
            var n = JSON.parse(u("tmp_g_config"));
            "object" == typeof n && null !== n && "undefined" != typeof n.fav && "undefined" != typeof n.fav.shopTitle && (e = decodeURIComponent(n.fav.shopTitle))
        }
        return e
    }

    function b(e) {
        return e = e.replace(/(?:\r\n|\r|\n)/g, " "), e = e.trim()
    }

    function _() {
        var e = "", t = $(".tb-shop-name a, .shop-name a.shop-name-link");
        return "undefined" != typeof t && (e = $(t).attr("href")), e = c(e)
    }

    function w() {
        var e = "", t = $(".shopLink");
        if ($(t).text() == '') {
            t = $("a.slogo-shopname");
        }
        if ($(t).text() == '') {
            t = $("div.hd-shop-name > a");
        }
        if ("undefined" != typeof t && (e = $(t).text()), !e.length) {
            var n = JSON.parse(u("tmp_g_config"));
            "object" == typeof n && null !== n && "undefined" != typeof n.sellerNickName && (e = decodeURIComponent(n.sellerNickName))
        }
        return e
    }

    function E() {
        var e = "", t = $(".shopLink");
        return "undefined" != typeof t && t.length || (t = $("a.slogo-shopname")), "undefined" != typeof t && t.length && (e = $(t).attr("href")), e = c(e)
    }

    function x() {
        var e = "",
            t = $(".app-common_supplierInfoSmall, .app-import_supplierInfoSmall, .app-smt_supplierInfoSmall, .app-offerdetail_topbar, .app-yuan_supplierInfoSmall");
        if ("undefined" != typeof t && t.length && (t = t.attr("data-view-config"), t.length && (t = JSON.parse(t)), "object" == typeof t && "undefined" != typeof t.loginId && (e = t.loginId)), "undefined" !== e && 0 === e.length && $("meta[property='og:product:nick']").length && (e = $("meta[property='og:product:nick']").attr("content"), e.length)) {
            var n = 0, i = e.indexOf("url=");
            i > 0 && (e = e.slice(n, i)), e = e.replace("name=", ""), e = e.replace(";", ""), e = e.trim()
        }

        if (e == "") {
            var container = document.getElementById('pi-component-container');
            if (container) {
                var spanArray = container.getElementsByTagName('span');
                if (spanArray.length > 0) {
                    e = spanArray[0].innerHTML;
                }
            } else {
                container = document.getElementsByClassName("logo-name")[0];
                if (container) {
                    var aArray = container.getElementsByTagName('a');
                    if (aArray && aArray.length > 0) {
                        e = aArray[0].innerHTML;
                    }
                }
            }
        }

        if (e == "") {
            e = "Không xác định";
        }
        return e
    }

    function C() {
        var e = "",
            t = $(".app-offerdetail_topbar, .app-import_topbar, .app-smt_topbar, .app-offerdetail_topbar, .app-common_topbar");
        if ("undefined" != typeof t && "undefined" !== t && t.length && (t = $(t).attr("data-view-config"), t.length ? (t = JSON.parse(t), "object" == typeof t && "undefined" != typeof t.currentDomainUrl && (e = t.currentDomainUrl)) : e = $("input.currentdomain").val()), ("undefined" === e || null === e || !e.length) && $("meta[property='og:product:nick']").length && (t = $('meta[property="og:product:nick"]'), "undefined" !== t && null !== t && t.length)) {
            t = t.attr("content");
            var n = t.indexOf("url=");
            t = t.slice(n + 4, -1), e = t.trim()
        }

        if (e == "") {
            var container = document.getElementsByClassName('primary-row-link');
            if (container && container.length) {
                e = container[0].href;
            } else {
                container = document.getElementsByClassName("logo-name")[0];
                if (container) {
                    var aArray = container.getElementsByTagName('a');
                    if (aArray && (aArray.length > 0)) {
                        e = aArray[0].href;
                    }
                }
            }

            if (e != "") {
                let domain = (new URL(e));
                e = domain.hostname;
            }
        }

        if (e == "") {
            e = "detail.1688.com";
        }
        return e = c(e)
    }

    function D() {
        var e = {img: "", list: []}, t = $(".price td .value:first, .price-now");
        t = "undefined" != typeof t ? parseFloat(t.text()) : 0;
        var n = $(".unit-detail-amount-control .amount-input");
        n = "undefined" != typeof n ? parseInt(n.val()) : 0;
        var i = {
            color: "",
            isMix: "false",
            max: "",
            min: "0",
            mixAmount: "0",
            mixBegin: "0",
            mixNumber: "0",
            price: t,
            qty: n,
            size: "",
            skuName: "",
            wsRuleNum: "",
            wsRuleUnit: ""
        };
        e.list.push(i);
        var o = $(".mod-detail-gallery .tab-pane .box-img img");
        return "undefined" != typeof o && (o = o.attr("src")), "undefined" != typeof o && o.length && (e.img = o), e
    }

    function N() {
        var e, t, n, i = [], o = [];
        var r = $(".detail-gallery-preview img").attr("src");

        if (!r) {
            r = $(".detail-gallery-turn-wrapper.prepic-active img.detail-gallery-img").attr("src");
        }

        if (!r) {
            r = $(".tab-pane img").attr("src");
        }

        var s = $("ul.list-leading div.active a.selected img").attr("alt");
        if (!s) {
            s = $(".list-leading .active .selected").attr("title");
        }
        if (!s) {
            s = $("div.prop-item div.active div.prop-name").attr("title");
        }

        "undefined" == typeof s && (s = ""), "undefined" != typeof r && (r = c(r));

        var a, l, u = $(".table-sku tr");
        if (u.length) {
            for (var h = 0; h < u.length; h++) {
                l = u[h];
                var p = "", d = $(l).find(".name .image").data("imgs");
                "object" == typeof d && "undefined" != typeof d && (p = c(d.preview)), a = $(l).data("sku-config"), n = $(l).find(".amount-input").val(), n = parseInt(n), "undefined" != typeof n && n > 0 && (e = $(l).find(".price .value").text(), t = $(l).find(".name span").text(), a.qty = n, a.price = parseFloat(e), a.color = s, a.image = p, o.push(a))
            }
        } else {
            u = $(".sku-item-wrapper");
            if (u.length) {
                for (var h = 0; h < u.length; h++) {
                    l = u[h];
                    a = {
                        color: "",
                        isMix: "false",
                        max: "",
                        min: "0",
                        mixAmount: "0",
                        mixBegin: "0",
                        mixNumber: "0",
                        price: t,
                        qty: "0",
                        size: "",
                        skuName: "",
                        wsRuleNum: "",
                        wsRuleUnit: ""
                    };

                    n = $(l).find('.next-input-group-auto-width>input').val();
                    n = parseInt(n);
                    if ("undefined" != typeof n && n > 0) {
                        e = $(l).find(".sku-item-left .discountPrice-price").text().replace(/[^0-9\.]/g, '');
                        t = $(l).find(".sku-item-left .sku-item-name").text();
                        a.qty = n;
                        a.price = parseFloat(e);
                        a.color = s;
                        a.size = t;
                        o.push(a)
                    }
                }
            }
        }

        var f = {color: s, img: r, list: o};
        return i.push(f), i
    }

    function k() {
        return "1688" == oe ? O() : T()
    }

    function O() {
        var e = [];
        $("tr.price td[data-range]").each(function (t, n) {
            var i = $(n).attr("data-range");
            "undefined" == typeof i ? e = parseFloat($(n).find(".value").text()) : (i = $.parseJSON(i), e.push(i))
        });

        if ("object" == typeof e && 0 == e.length) {
            e = parseFloat($("#mod-detail-price").find("span.num").text());
        }

        if (isNaN(e) && "object" != typeof e) {
            e = $(".table-sku").find("tr:first").find("td.price").find("em.value").text();
            e = parseFloat(e);
        }

        if (isNaN(e) && "object" != typeof e) {
            var price = [];
            $('div.price-box span.price-text').each(function (ind, el) {
                var priceItem = parseFloat($(el).text());
                if (priceItem > 0) {
                    price.push(priceItem);
                }
            });

            if (price.length) {
                e = Math.min.apply(null, price);
            }
        }

        if (isNaN(e) && "object" != typeof e) {
            e = $(".sku-item-wrapper").find("div.sku-item-wrapper:first").find("div.sku-item-left").find("div.discountPrice-price").text().replace(/[^0-9\.]/g, '');
            e = parseFloat(e);
        }

        return e;
    }

    function T() {
        var e, t = $(W[oe].crawle.originPrice), n = $(W[oe].crawle.promoPrice), i = t.text(), o = n.text(), r = 0,
            s = 0, a = 0, l = 0;
        if (!o) {
            console.log("cuongnh111");
            o = "0";
        }
        return i.indexOf("-") > -1 ? (e = i.split("-"), r = parseFloat(e[0]), s = parseFloat(e[1]), i = 0) : i = parseFloat(i), o.indexOf("-") > -1 ? (e = o.split("-"), a = parseFloat(e[0]), l = parseFloat(e[1]), o = 0) : o = parseFloat(o), {
            orgPrice: i,
            proPrice: o,
            lowPrice: r,
            highPrice: s,
            lowPromo: a,
            highPromo: l
        }
    }

    function I(e, t) {
        o(B, function (n) {
            switch (U = [], e) {
                case"add":
                    if ($.isArray(t)) for (var i = t.length - 1; i >= 0; i--) U.push(t[i]); else U.push(t);
                    break;
                case"remove":
                    U.removeAt(t)
            }
            var o = {};
            o[B] = U, r(o, function () {
                L(), a(), console.log("[thqc] Products saved!")
            })
        })
    }

    function P() {
        var e = $("#J_SKU .J_SKU, .tb-sku .J_TSaleProp, #J_isku .tb-skin .tb-prop");
        if (0 == e.length) return !0;
        var t = 0;
        t = $("#J_SKU dl, .tb-sku .tm-sale-prop, #J_isku .tb-skin .tb-prop").length;
        var n = 0;
        return n = $("#J_SKU .J_SKU.tb-selected, .tb-sku .J_TSaleProp .tb-selected, #J_isku .tb-skin .tb-prop .J_TSaleProp .tb-selected").length, 0 == n ? (g("Thông báo", "Bạn chưa chọn thuộc tính cho sản phẩm.", "warning"), m(), !1) : n > 0 && t > n ? (g("Thông báo", "Bạn phải chọn đầy đủ thuộc tính cho sản phẩm.", "warning"), m(), !1) : n > 0 && t == n
    }

    function M() {
        var e = 0, t = 0, n = 1;
        if ($(".de-cal-content .amount-input").length) {
            t = $(".de-cal-content .amount-input").val();
        } else {
            if ($(".unit-detail-amount-control .amount-input").length) {
                t = $(".unit-detail-amount-control .amount-input").val();
            }
        }

        if (("undefined" == typeof t) || t == 0) {
            t = 0;
            $('.next-input-group-auto-width>input').each(function () {
                console.log("cuongnh: " + $(this).val());
                if ($(this).val() != "") {
                    t = t + parseInt($(this).val());
                }
            });
        }
        console.log("cuongnh: " + t);

        var i = "";
        if (i = $(".unit-detail-freight-cost").attr("data-unit-config"), "undefined" != typeof i && (i = JSON.parse(i)), "object" == typeof i && (n = parseInt(i.beginAmount)), t < n) return g("Thông báo", "Shop yêu cầu mua tối thiểu " + n + " sản phẩm.", "warning"), m(), !1;
        if (i = $(".unit-box .unit-text").text(), "undefined" != typeof i && (i = i.replace(/[^0-9\.]/g, '')), (n = parseInt(i)), t < n) return g("Thông báo", "Shop yêu cầu mua tối thiểu " + n + " sản phẩm.", "warning"), m(), !1;

        if (e = $(".list-leading .unit-detail-spec-operator").length, e > 0 && (e = 0, e = $(".list-leading .active").length, 0 == e)) return g("Thông báo", "Bạn chưa chọn thuộc tính cho sản phẩm.", "warning"), m(), !1;
        if (e = $(".prop-item .prop-item-inner-wrapper").length, e > 0 && (e = 0, e = $(".prop-item .active").length, 0 == e)) return g("Thông báo", "Bạn chưa chọn thuộc tính cho sản phẩm.", "warning"), m(), !1;

        var o = 0, r = $(".amount .amount-input, .obj-amount .amount-input");
        return "undefined" != typeof r && r.length ? r.each(function (e, t) {
            o += t.value
        }) : (r = $(".area-panel .unit-detail-amount-control .amount-input"), "undefined" != typeof r && (o = r.val())), 0 != o || (g("Thông báo", "Bạn chưa chọn số lượng sản phẩm cần mua.", "warning"), m(), !1)
    }

    function R() {
        return d(), "1688" == oe ? A() : !!P() && S()
    }

    function S() {
        var e = "", t = "", n = "";
        "taobao" === oe ? (n = $("#J_ThumbView, #J_ImgBooth").attr("alt"), "undefined" == typeof n && (n = $("#J_Title .tb-main-title").data("title")), e = y(), t = _()) : "tmall" === oe && (n = $("#J_ThumbView, #J_ImgBooth").attr("alt"), e = w(), t = E());
        var i = $(W[oe].crawle.image).attr("src"), o = $(W[oe].crawle.size).next().find(".tb-selected").data("pv"),
            r = $(W[oe].crawle.color).next().find(".tb-selected").data("pv"),
            s = $(W[oe].crawle.more_pro1).next().find(".tb-selected").data("pv"),
            a = $(W[oe].crawle.more_pro2).next().find(".tb-selected").data("pv");
        "undefined" == typeof o && (o = $(W[oe].crawle.size).next().find(".tb-selected").data("value")), "undefined" == typeof o && (o = ""), "undefined" == typeof r && (r = $(W[oe].crawle.color).next().find(".tb-selected").data("value")), "undefined" == typeof r && (r = ""), "undefined" == typeof s && (s = $(W[oe].crawle.more_pro1).next().find(".tb-selected").data("value")), "undefined" == typeof s ? s = "" : (r += s, console.log("color1", r)), "undefined" == typeof a && (a = $(W[oe].crawle.more_pro2).next().find(".tb-selected").data("value")), "undefined" == typeof a ? a = "" : (r += a, console.log("color2", r));
        var u = $(W[oe].crawle.size).next().find(".tb-selected a").text().trim(),
            h = $(W[oe].crawle.color).next().find(".tb-selected a").text().trim() + $(W[oe].crawle.more_pro1).next().find(".tb-selected a").text().trim() + $(W[oe].crawle.more_pro2).next().find(".tb-selected a").text().trim(),
            d = $(W[oe].crawle.originPrice).text(), f = $(W[oe].crawle.promoPrice).text();
        d = p(d), f = p(f);
        var m = d;
        f && !isNaN(f) && (m = f);
        var g = "";
        "taobao" == oe ? g = v(o, r) : "tmall" == oe && (g = l("skuId"));

        console.log("cuongnh12121: " + t);

        var b = {
            rate: ie.rate,
            name: n,
            pro_link: c(window.location.href),
            image: c(i),
            price: m,
            price_arr: "",
            size: o,
            sizetxt: u,
            color: r,
            colortxt: h,
            pro_properties: g,
            amount: parseInt($(W[oe].crawle.amount).val()),
            beginAmount: 1,
            shop_nick: e,
            shop_link: c(t),
            domain: oe,
            site: oe,
            note: $("#pro-thqc-note .thqcNote").val(),
            count: !1,
            method: "Chrome Extension"
        };
        I("add", b)
    }

    function A() {
        if (!M()) return !1;
        var e = [], t = "", n = "", i = "";
        i = $(".mod-detail-gallery img").attr("alt"), t = x(), n = C();

        var o = 1, r = $(".unit-detail-freight-cost").attr("data-unit-config");
        "undefined" != typeof r && (r = JSON.parse(r)), "object" == typeof r && (o = parseInt(r.beginAmount)), z = N(), 0 == z[0].list.length && (z = [], z.push(D())), z.forEach(function (r) {
            console.log('123456', r);
            var s = r.img.split("/");
            s = s[s.length - 1], s = s.split(".")[0], s = s.split("_"), r.list.forEach(function (a) {
                var l = r.img;
                "undefined" != typeof a.image && a.image && (l = a.image), l = c(l);
                var sizetxt = "";
                if ("undefined" != typeof a.skuName) {
                    sizetxt = sizetxt + a.skuName;
                }
                if ("undefined" != typeof a.size) {
                    sizetxt = sizetxt + a.size;
                }

                var u = {
                    proId: parseInt(s[1], 10),
                    skullId: parseInt(s[0], 10),
                    rate: ie.rate,
                    pro_link: c(window.location.href),
                    image: l,
                    name: i,
                    price: a.price,
                    price_arr: k(),
                    size: sizetxt,
                    sizetxt: sizetxt,
                    color: a.color,
                    colortxt: a.color,
                    amount: a.qty,
                    beginAmount: o,
                    shop_nick: t,
                    shop_link: c(n),
                    site: oe,
                    domain: oe,
                    note: $("#pro-thqc-note .thqcNote").val(),
                    count: !1,
                    method: "Chrome Extension"
                };
                e.push(u)
            })
        }), I("add", e)
    }

    function j() {
        var e = window.location.hostname;
        return e = e.toLowerCase(), e == Q
    }

    function L() {
        o(q, function (e) {
            if (typeof e.tbex_thqc_token === 'undefined' || e.tbex_thqc_token === null) {
                alert('Phiên làm việc hết hạn! Bạn cần đăng nhập nguonhang.net trước khi đặt hàng!');
                window.location.href = "https://nguonhang.net/order/login";
                return;
            }
            "undefined" != typeof e.tbex_thqc_token && null !== e.tbex_thqc_token && o(B, function (t) {
                var n = e.tbex_thqc_token[0], o = t.tbex_thqc;
                o = JSON.stringify(o), $.ajax({
                    method: "POST",
                    url: ie.apiAddCart,
                    data: {cart: o, tk: n},
                    dataType: 'json',
                    beforeSend: function (xhr) {
                        $(".btn-thqc-cart-url").addClass("disabled")
                    }
                }).done(function (e, t, n) {
                    $(".btn-thqc-cart-url").removeClass("disabled"), e.status && i()
                })
            })
        })
    }

    function setShop() {
        o(q, function (tk) {
            if (typeof tk.tbex_thqc_token === 'undefined' || tk.tbex_thqc_token === null) {
                alert('Phiên làm việc hết hạn! Bạn cần đăng nhập nguonhang.net trước khi đặt hàng!');
                window.location.href = "https://nguonhang.net/order/login";
                return;
            }
            var n = tk.tbex_thqc_token[0];
            var e = "", t = "";
            "1688" == oe && (e = x(), t = C()), "taobao" == oe && (e = y(), t = _()), "tmall" == oe && (e = w(), t = E()), $.ajax({
                method: "POST",
                url: ie.apiShopUrl,
                dataType: 'json',
                data: {name: e, url: t, tk: n}
            }).done(function (e, t, n) {
                var i = "";
                if (e.data.rate) {
                    var o = e.data.rate;
                    ie.rate = o, o = o.format(), $(ne).text(o);
                }
                e.data.rating && (i += 'Độ tin cậy: <span class="tong-quan">' + e.data.rating + "</span>");
                var r = "";
                e.data.complain && (r += 'Tỷ lệ khiếu nại: <span class="khieu-nai">' + e.data.complain + "</span>");
                var s = "";
                e.data.in_cn && (s += 'Thời gian Shop phát hàng (ngày): <span class="kho-cn">' + e.data.in_cn + "</span>");
                var a = "", l = i + r + s + a;
                "undefined" !== e.data.od_success && e.data.od_success || (l = 'Độ tin cậy: <span class="tong-quan">Chưa có đánh giá</span>'), $("#cart-thqc-shop-info").append(l), showInfo()
            })
        })
    }

    function showInfo() {
        if (j()) return !1;
        var e = "", t = "", n = k(), i = "";
        if ("1688" == oe) {
            if ($(".mod-detail-purchasing").length) {
                t = $(".mod-detail-purchasing").attr("data-mod-config")
                if (t.length > 0) {
                    t = JSON.parse(t);
                }
                if ("object" == typeof t) {
                    t = parseInt(t.max);
                }
                if ("number" == typeof t) {
                    e += '<dl><dd style="width:100%"><span class="text-danger">Shop hiện còn <b>' + t + "</b> sản phẩm</span></dd></dl>";
                }
            }

            if ("number" == typeof n) {
                i = Math.round(n * ie.rate).format();
                // console.log("cuongnh111", i, n);
            } else {
                // console.log("cuongnh121", i, n);
                if (n.length > 0) {
                    parseInt(n[0].begin) > 1 && (e += '<dl><dd style="width:100%"><span class="text-danger">Shop yêu cầu mua tối thiểu ' + n[0].begin + " sản phẩm</span></dd></dl>");
                    for (var o = 0; o <= n.length - 1; o++) e += n[o].end.length > 0 ? "<dl><dd>Mua: " + n[o].begin + " - " + n[o].end + ' sản phẩm</dd><dd>Giá: <span class="tbe-color-price">¥' + n[o].price + "</span></dd></dl>" : "<dl><dd>Mua: &gt;" + n[o].begin + ' sản phẩm</dd><dd>Giá: <span class="tbe-color-price">¥' + n[o].price + "</span></dd></dl>"
                }
                i = Math.round(n[0].price * ie.rate).format();
            }
            if (isNaN(i) && typeof n == 'number') {
                i = (Math.round(n * ie.rate)).format();
            }
        } else {
            // console.log("cuongnh131", i, n);
            t = $("#J_EmStock, #J_SpanStock").text(), t.length > 0 && (t = H(t), t = parseInt(t)), "number" == typeof t && (e += "tmall" == oe ? '<dl><dd style="width:100%"><span class="text-danger">Shop hiện còn <b>' + t + "</b> sản phẩm</span></dd></dl>" : '<dl><dd style="width:100%"><span class="text-danger">Shop giới hạn mua tối đa <b class="tbe-color-price">' + t + "</b> sản phẩm</span></dd></dl>"), i = Math.round(n.orgPrice * ie.rate).format(), ((n.orgPrice > 0 && n.proPrice > 0 && n.orgPrice > n.proPrice) || ((0 == n.orgPrice || isNaN(n.orgPrice)) && n.proPrice > 0)) ? i = Math.round(n.proPrice * ie.rate).format() : (n.lowPrice > 0 && n.highPrice > 0 && (i = Math.round(n.lowPrice * ie.rate).format() + " - " + Math.round(n.highPrice * ie.rate).format()), n.lowPromo > 0 && n.highPromo > 0 && (i = Math.round(n.lowPromo * ie.rate).format() + " - " + Math.round(n.highPromo * ie.rate).format()));
            // console.log("1cuongnh11", i);
        }

        e.length > 0 && (e = '<div class="bg-info">' + e + "</div>");
        // console.log("cuongnh1", i, n);
        var r = [
            '<div id="tbe-info-shop">',
            '<h5>NGUONHANG.NET</h5>',
            '<img style="width:70px;" src="' + chrome.runtime.getURL("images/144x144.png") + '" alt="' + te + '" />',
            "<ul>", '<li>Giá bán: <b class="tbe-color-price">' + i + "</b> đ</li>",
            '<li>Tỷ giá: <span class="tbe-rate tbe-color-price">' + ie.rate.format() + "</span> đ/tệ</li>",
            "</ul>",
            e,
            '<div class="tbe-info-warning"><p>(!!) Vui lòng chọn đầy đủ thông tin sản phẩm ở bên dưới để xem giá chuẩn.</p><p>(!!) không dùng Google Translate khi thêm sản phẩm.</p></div>',
            "</div>"].join("");
        $("#J_Title, .tb-detail-hd, #mod-detail-price, .od-pc-offer-title-contain").append(r);

        $(".obj-order").hide();
        $(".order-button-wrapper").hide();
        $(".tb-action").hide();
        $("#J_juValid").hide();
    }

    function H(e) {
        var t = e.replace(/[^0-9]/g, "");
        return t
    }

    function sleep(milliseconds) {
        const date = Date.now();
        let currentDate = null;
        do {
            currentDate = Date.now();
        } while (currentDate - date < milliseconds);
    }

    var W = {
            taobao: {
                translate: {
                    originPrice: "#J_PriceName",
                    promoPrice: "#J_PriceName",
                    size: 'dt:contains("适用年龄"), dt:contains("尺碼"), dt:contains("尺寸"), dt:contains("尺码"), dt:contains("参考身高"), dt:contains("鞋码"), dt:contains("大小描述"), dt:contains("电压"), dt:contains("内存容量")',
                    color: 'dt:contains("顏色"), dt:contains("颜色分类"), dt:contains("颜色"),dt:contains("转速"), dt:contains("监控摄像头路数")',
                    amount: 'dt:contains("數量"), dt:contains("数量")',
                    unit: ".tb-amount-widget .mui-amount-unit"
                },
                crawle: {
                    originPrice: "#J_priceStd .tb-rmb-num, #J_StrPrice .tb-rmb-num",
                    promoPrice: "#J_PromoPrice .tb-rmb-num, #J_PromoPriceNum",
                    image: "#J_ThumbView, #J_ImgBooth",
                    shop_nick: ".tb-shop-name a",
                    shop_link: ".tb-shop-name a",
                    amount: "#J_IptAmount",
                    size: 'dt:contains("Kích thước"), dt:contains("Kích thước"), dt:contains("kích thước"), dt:contains("Size"), dt:contains("size")',
                    color: 'dt:contains("Màu sắc"), dt:contains("màu sắc"), dt:contains("màu số"), dt:contains("Color"), dt:contains("color")',
                    more_pro1: 'dt:contains("清晰度")',
                    more_pro2: 'dt:contains("焦距")',
                    lowPrice: 'span[itemprop="lowPrice"]',
                    highPrice: 'span[itemprop="highPrice"]'
                }
            },
            tmall: {
                translate: {
                    originPrice: 'dt:contains("價格"), dt:contains("专柜价"), dt:contains("价格")',
                    promoPrice: 'dt:contains("促銷價"), dt:contains("淘宝价"), dt:contains("促销价")',
                    size: 'dt:contains("尺碼"), dt:contains("尺寸"), dt:contains("尺码"), dt:contains("套餐類型"), dt:contains("参考身高"), dt:contains("鞋码"), dt:contains("大小描述"), dt:contains("电压")',
                    color: 'dt:contains("顏色"), dt:contains("颜色"), dt:contains("转速")',
                    amount: 'dt:contains("數量"), dt:contains("数量")',
                    unit: ".tb-amount-widget .mui-amount-unit"
                },
                crawle: {
                    originPrice: "#J_DetailMeta > div.tm-clear > div.tb-property > div > div.tm-fcs-panel > dl.tm-tagPrice-panel > dd > span, #J_StrPriceModBox > dd > span",
                    promoPrice: "#J_PromoPrice > dd > div > span.tm-price, #J_PromoBox > div > span",
                    image: "#J_ThumbView, #J_ImgBooth",
                    shop_nick: ".shopLink",
                    shop_link: ".shopLink",
                    amount: "#J_Amount input",
                    size: 'dt:contains("Kích thước"), dt:contains("kích thước"), dt:contains("Size"), dt:contains("size")',
                    color: 'dt:contains("Màu sắc"), dt:contains("màu sắc"), dt:contains("màu số"), dt:contains("Color"), dt:contains("color")',
                    more_pro1: 'dt:contains("清晰度")',
                    more_pro2: 'dt:contains("焦距")',
                    lowPrice: "#J_PromoPrice .tm-price",
                    highPrice: "#J_PromoPrice .tm-price"
                }
            },
            1688: {
                translate: {
                    originPrice: "tr.price > td.price-title",
                    promoPrice: "tr.price > td.price-title",
                    size: ".d-content .obj-sku .obj-title",
                    color: ".d-content .obj-leading .obj-title",
                    amount: "",
                    unit: ""
                },
                crawle: {
                    originPrice: ".tm-price-panel .tm-price",
                    promoPrice: ".tm-promo-panel .tm-price",
                    image: ".mod-detail-gallery img, .detail-gallery-preview img",
                    shop_nick: "#usermidid",
                    shop_link: ".currentdomain, .enname",
                    amount: "#J_Amount input",
                    size: "",
                    color: 'span.obj-title:contains("Màu sắc"), span.obj-title:contains("màu sắc"), dt:contains("Color"), dt:contains("color")',
                    lowPrice: "#J_PromoPrice .tm-price",
                    highPrice: "#J_PromoPrice .tm-price"
                }
            }
        }, U = [], z = [], B = "tbex_thqc", q = "tbex_thqc_token", Y = "REQUEST_DATA", K = "CART_TOKEN",
        X = "CLEAR_DATA", G = chrome.runtime.getManifest(), J = (G.version, h(G.homepage_url)), Q = G.short_name,
        Z = J + "/api/shop/create", ee = J + "/api/cart/create", te = "THQC", ne = ".tbe-rate", ie = {
            rate: G.input_components.rate,
            apiShopUrl: Z,
            apiAddCart: ee,
            urlGetRate: J + "/san-pham/lay-ty-gia",
            cartUrl: J + "/../../cart",
            helpUrl: J + "/danh-muc-bai-viet/chi-tiet-20-huong-dan-tao-don-hang-tren-he-thong",
            reportUrl: J + "/bao-cao/gio-hang",
            logoUrl: J + "/images/extention_logo.png",
            logoUrl2: J + "/images/logo_export.png",
            urlCurrentVersion: J + "/san-pham/current-tool-version",
            currentVer: chrome.runtime.getManifest().version,
            allowedDomains: ["tmall", "taobao", "1688"],
            title: te
        }, oe = s(), re = "default", se = "THÊM VÀO GIỎ HÀNG", ae = e("react"), le = e("react-dom"),
        ue = $('<div id="cart-thqc-parent"></div>'), ce = $('<div id="pro-thqc-note"></div>'),
        he = $('<div id="cart-thqc-warning"></div>'),
        pe = $("#J_box_buycart, #J_juValid, .tb-action, .obj-order, .order-button-children, .order-button-wrapper, .od-pc-offer-payment-contain"),
        de = ae.createClass({
            displayName: "TextNote", render: function () {
                return ae.createElement("textarea", {
                    className: "thqcNote",
                    name: "pro-thqc-note",
                    placeholder: "Ghi chú sản phẩm",
                    rows: "4",
                    cols: "50"
                })
            }
        }), fe = ae.createClass({
            displayName: "TextWarning", render: function () {
                return ae.createElement("span", {className: "text-center success"}, this.props.message)
            }
        }), me = ae.createClass({
            displayName: "BtnAddPro", cartLink: function () {
                return window.open(ie.cartUrl)
            }, render: function () {
                var e = (chrome.runtime.getURL("images/128x128.png"), chrome.runtime.getURL("images/32x32.png"));
                return ae.createElement("div", null, ae.createElement("a", {
                    className: "btn-tbex btn-tbex-small btn-tbex-default btn-thqc-cart-url",
                    onClick: this.cartLink
                }, ae.createElement("img", {src: e}), ae.createElement("sup", {className: "pro-thqc-add-new"}, " ")), ae.createElement("a", {
                    className: "btn-tbex btn-tbex-small btn-tbex-success btn-thqc-add",
                    onClick: R
                }, this.props.btnname))
            }
        });

    "undefined" != typeof window && (window.React = ae), window.addEventListener("message", function (e) {
        if (e.origin == 'https://nguonhang.net') {
            var t = {};
            switch (e.data.type) {
                case K:
                    var n = e.data.id;
                    t[q] = [n], r(t, function () {
                        console.info("set local cart token!")
                    }), e.source.postMessage({error: 0, message: "set local data _token!"}, e.origin);
                    break;
                case Y:
                    o(B, function (t) {
                        t ? e.source.postMessage({
                            error: 2,
                            message: "",
                            data: t
                        }, e.origin) : e.source.postMessage({error: 1, message: msg}, e.origin)
                    });
                    break;
                case X:
                    t[B] = [], r(t, function () {
                        console.info("All data was cleared!")
                    }), e.source.postMessage({error: 0, message: "All data was cleared!"}, e.origin)
            }
        }
    }, !1), $(document).ready(function () {
        pe.ready(function () {
            if ("taobao" === oe) {
                $(".sufei-dialog-kissy").remove();
                $("#J_isku .tb-skin").on("click", "li", function (e) {
                    e.preventDefault();
                    $(this).siblings("li").removeClass("tb-selected");
                    $(this).addClass("tb-selected");
                });
            }

            if (f() && !j()) {
                d();
                $(W[oe].translate.originPrice).text("Giá");
                $(W[oe].translate.promoPrice).text("Giá bán");
                $(W[oe].translate.size).text("Kích thước"), $(W[oe].translate.color).text("Màu sắc"), $(W[oe].translate.amount).text("Số lượng");
                $(W[oe].translate.unit).text("sản phẩm");
                if (pe.length == 0) {
                    sleep(2000);
                    pe = $("#J_box_buycart, #J_juValid, .tb-action, .obj-order, .order-button-children, .order-button-wrapper, .od-pc-offer-payment-contain");
                    console.log('pe', pe);
                }
                if (pe.length == 0) {
                    sleep(2000);
                    pe = $("#J_box_buycart, #J_juValid, .tb-action, .obj-order, .order-button-children, .order-button-wrapper, .od-pc-offer-payment-contain");
                    console.log('pe', pe);
                }
                if (pe.length == 0) {
                    sleep(2000);
                    pe = $("#J_box_buycart, #J_juValid, .tb-action, .obj-order, .order-button-children, .order-button-wrapper, .od-pc-offer-payment-contain");
                    console.log('pe', pe);
                }
                pe.before(ce);
                pe.before(he);
                pe.before(ue);
                le.render(ae.createElement(de, null), document.getElementById("pro-thqc-note"));
                le.render(ae.createElement(fe, {message: re}), document.getElementById("cart-thqc-warning"));
                le.render(ae.createElement(me, {btnname: se}), document.getElementById("cart-thqc-parent"));
                setShop();
            }
        });
    }), Number.prototype.format = function () {
        var e = this + "", t = e.split(","), n = t[0], i = "";
        i = t.length > 1 ? "," + t[1] : "";
        for (var o = /(\d+)(\d{3})/; o.test(n);) n = n.replace(o, "$1,$2");
        return n + i
    }
}
