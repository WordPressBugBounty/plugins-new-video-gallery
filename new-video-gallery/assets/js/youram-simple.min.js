/*------------------------------------------------------------------
YouRam Simple from HanuCodes
------------------------------------------------------------------*/

/*--------------------------
Magnific Popup
--------------------------*/
(function(a) {
    typeof define == "function" && define.amd ? define(["jquery"], a) : typeof exports == "object" ? a(require("jquery")) : a(window.jQuery || window.Zepto)
})(function(a) {
    var b = "Close",
        c = "BeforeClose",
        d = "AfterClose",
        e = "BeforeAppend",
        f = "MarkupParse",
        g = "Open",
        h = "Change",
        i = "mfp",
        j = "." + i,
        k = "mfp-ready",
        l = "mfp-removing",
        m = "mfp-prevent-close",
        n, o = function() {},
        p = !!window.jQuery,
        q, r = a(window),
        s, t, u, v, w = function(a, b) {
            n.ev.on(i + a + j, b)
        },
        x = function(b, c, d, e) {
            var f = document.createElement("div");
            return f.className = "mfp-" + b, d && (f.innerHTML = d), e ? c && c.appendChild(f) : (f = a(f), c && f.appendTo(c)), f
        },
        y = function(b, c) {
            n.ev.triggerHandler(i + b, c), n.st.callbacks && (b = b.charAt(0).toLowerCase() + b.slice(1), n.st.callbacks[b] && n.st.callbacks[b].apply(n, a.isArray(c) ? c : [c]))
        },
        z = function(b) {
            if (b !== v || !n.currTemplate.closeBtn) n.currTemplate.closeBtn = a(n.st.closeMarkup.replace("%title%", n.st.tClose)), v = b;
            return n.currTemplate.closeBtn
        },
        A = function() {
            a.magnificPopup.instance || (n = new o, n.init(), a.magnificPopup.instance = n)
        },
        B = function() {
            var a = document.createElement("p").style,
                b = ["ms", "O", "Moz", "Webkit"];
            if (a.transition !== undefined) return !0;
            while (b.length)
                if (b.pop() + "Transition" in a) return !0;
            return !1
        };
    o.prototype = {
        constructor: o,
        init: function() {
            var b = navigator.appVersion;
            n.isLowIE = n.isIE8 = document.all && !document.addEventListener, n.isAndroid = /android/gi.test(b), n.isIOS = /iphone|ipad|ipod/gi.test(b), n.supportsTransition = B(), n.probablyMobile = n.isAndroid || n.isIOS || /(Opera Mini)|Kindle|webOS|BlackBerry|(Opera Mobi)|(Windows Phone)|IEMobile/i.test(navigator.userAgent), s = a(document), n.popupsCache = {}
        },
        open: function(b) {
            var c;
            if (b.isObj === !1) {
                n.items = b.items.toArray(), n.index = 0;
                var d = b.items,
                    e;
                for (c = 0; c < d.length; c++) {
                    e = d[c], e.parsed && (e = e.el[0]);
                    if (e === b.el[0]) {
                        n.index = c;
                        break
                    }
                }
            } else n.items = a.isArray(b.items) ? b.items : [b.items], n.index = b.index || 0;
            if (n.isOpen) {
                n.updateItemHTML();
                return
            }
            n.types = [], u = "", b.mainEl && b.mainEl.length ? n.ev = b.mainEl.eq(0) : n.ev = s, b.key ? (n.popupsCache[b.key] || (n.popupsCache[b.key] = {}), n.currTemplate = n.popupsCache[b.key]) : n.currTemplate = {}, n.st = a.extend(!0, {}, a.magnificPopup.defaults, b), n.fixedContentPos = n.st.fixedContentPos === "auto" ? !n.probablyMobile : n.st.fixedContentPos, n.st.modal && (n.st.closeOnContentClick = !1, n.st.closeOnBgClick = !1, n.st.showCloseBtn = !1, n.st.enableEscapeKey = !1), n.bgOverlay || (n.bgOverlay = x("bg").on("click" + j, function() {
                n.close()
            }), n.wrap = x("wrap").attr("tabindex", -1).on("click" + j, function(a) {
                n._checkIfClose(a.target) && n.close()
            }), n.container = x("container", n.wrap)), n.contentContainer = x("content"), n.st.preloader && (n.preloader = x("preloader", n.container, n.st.tLoading));
            var h = a.magnificPopup.modules;
            for (c = 0; c < h.length; c++) {
                var i = h[c];
                i = i.charAt(0).toUpperCase() + i.slice(1), n["init" + i].call(n)
            }
            y("BeforeOpen"), n.st.showCloseBtn && (n.st.closeBtnInside ? (w(f, function(a, b, c, d) {
                c.close_replaceWith = z(d.type)
            }), u += " mfp-close-btn-in") : n.wrap.append(z())), n.st.alignTop && (u += " mfp-align-top"), n.fixedContentPos ? n.wrap.css({
                overflow: n.st.overflowY,
                overflowX: "hidden",
                overflowY: n.st.overflowY
            }) : n.wrap.css({
                top: r.scrollTop(),
                position: "absolute"
            }), (n.st.fixedBgPos === !1 || n.st.fixedBgPos === "auto" && !n.fixedContentPos) && n.bgOverlay.css({
                height: s.height(),
                position: "absolute"
            }), n.st.enableEscapeKey && s.on("keyup" + j, function(a) {
                a.keyCode === 27 && n.close()
            }), r.on("resize" + j, function() {
                n.updateSize()
            }), n.st.closeOnContentClick || (u += " mfp-auto-cursor"), u && n.wrap.addClass(u);
            var l = n.wH = r.height(),
                m = {};
            if (n.fixedContentPos && n._hasScrollBar(l)) {
                var o = n._getScrollbarSize();
                o && (m.marginRight = o)
            }
            n.fixedContentPos && (n.isIE7 ? a("body, html").css("overflow", "hidden") : m.overflow = "hidden");
            var p = n.st.mainClass;
            return n.isIE7 && (p += " mfp-ie7"), p && n._addClassToMFP(p), n.updateItemHTML(), y("BuildControls"), a("html").css(m), n.bgOverlay.add(n.wrap).prependTo(n.st.prependTo || a(document.body)), n._lastFocusedEl = document.activeElement, setTimeout(function() {
                n.content ? (n._addClassToMFP(k), n._setFocus()) : n.bgOverlay.addClass(k), s.on("focusin" + j, n._onFocusIn)
            }, 16), n.isOpen = !0, n.updateSize(l), y(g), b
        },
        close: function() {
            if (!n.isOpen) return;
            y(c), n.isOpen = !1, n.st.removalDelay && !n.isLowIE && n.supportsTransition ? (n._addClassToMFP(l), setTimeout(function() {
                n._close()
            }, n.st.removalDelay)) : n._close()
        },
        _close: function() {
            y(b);
            var c = l + " " + k + " ";
            n.bgOverlay.detach(), n.wrap.detach(), n.container.empty(), n.st.mainClass && (c += n.st.mainClass + " "), n._removeClassFromMFP(c);
            if (n.fixedContentPos) {
                var e = {
                    marginRight: ""
                };
                n.isIE7 ? a("body, html").css("overflow", "") : e.overflow = "", a("html").css(e)
            }
            s.off("keyup" + j + " focusin" + j), n.ev.off(j), n.wrap.attr("class", "mfp-wrap").removeAttr("style"), n.bgOverlay.attr("class", "mfp-bg"), n.container.attr("class", "mfp-container"), n.st.showCloseBtn && (!n.st.closeBtnInside || n.currTemplate[n.currItem.type] === !0) && n.currTemplate.closeBtn && n.currTemplate.closeBtn.detach(), n.st.autoFocusLast && n._lastFocusedEl && a(n._lastFocusedEl).focus(), n.currItem = null, n.content = null, n.currTemplate = null, n.prevHeight = 0, y(d)
        },
        updateSize: function(a) {
            if (n.isIOS) {
                var b = document.documentElement.clientWidth / window.innerWidth,
                    c = window.innerHeight * b;
                n.wrap.css("height", c), n.wH = c
            } else n.wH = a || r.height();
            n.fixedContentPos || n.wrap.css("height", n.wH), y("Resize")
        },
        updateItemHTML: function() {
            var b = n.items[n.index];
            n.contentContainer.detach(), n.content && n.content.detach(), b.parsed || (b = n.parseEl(n.index));
            var c = b.type;
            y("BeforeChange", [n.currItem ? n.currItem.type : "", c]), n.currItem = b;
            if (!n.currTemplate[c]) {
                var d = n.st[c] ? n.st[c].markup : !1;
                y("FirstMarkupParse", d), d ? n.currTemplate[c] = a(d) : n.currTemplate[c] = !0
            }
            t && t !== b.type && n.container.removeClass("mfp-" + t + "-holder");
            var e = n["get" + c.charAt(0).toUpperCase() + c.slice(1)](b, n.currTemplate[c]);
            n.appendContent(e, c), b.preloaded = !0, y(h, b), t = b.type, n.container.prepend(n.contentContainer), y("AfterChange")
        },
        appendContent: function(a, b) {
            n.content = a, a ? n.st.showCloseBtn && n.st.closeBtnInside && n.currTemplate[b] === !0 ? n.content.find(".mfp-close").length || n.content.append(z()) : n.content = a : n.content = "", y(e), n.container.addClass("mfp-" + b + "-holder"), n.contentContainer.append(n.content)
        },
        parseEl: function(b) {
            var c = n.items[b],
                d;
            c.tagName ? c = {
                el: a(c)
            } : (d = c.type, c = {
                data: c,
                src: c.src
            });
            if (c.el) {
                var e = n.types;
                for (var f = 0; f < e.length; f++)
                    if (c.el.hasClass("mfp-" + e[f])) {
                        d = e[f];
                        break
                    } c.src = c.el.attr("data-mfp-src"), c.src || (c.src = c.el.attr("href"))
            }
            return c.type = d || n.st.type || "inline", c.index = b, c.parsed = !0, n.items[b] = c, y("ElementParse", c), n.items[b]
        },
        addGroup: function(a, b) {
            var c = function(c) {
                c.mfpEl = this, n._openClick(c, a, b)
            };
            b || (b = {});
            var d = "click.magnificPopup";
            b.mainEl = a, b.items ? (b.isObj = !0, a.off(d).on(d, c)) : (b.isObj = !1, b.delegate ? a.off(d).on(d, b.delegate, c) : (b.items = a, a.off(d).on(d, c)))
        },
        _openClick: function(b, c, d) {
            var e = d.midClick !== undefined ? d.midClick : a.magnificPopup.defaults.midClick;
            if (!e && (b.which === 2 || b.ctrlKey || b.metaKey || b.altKey || b.shiftKey)) return;
            var f = d.disableOn !== undefined ? d.disableOn : a.magnificPopup.defaults.disableOn;
            if (f)
                if (a.isFunction(f)) {
                    if (!f.call(n)) return !0
                } else if (r.width() < f) return !0;
            b.type && (b.preventDefault(), n.isOpen && b.stopPropagation()), d.el = a(b.mfpEl), d.delegate && (d.items = c.find(d.delegate)), n.open(d)
        },
        updateStatus: function(a, b) {
            if (n.preloader) {
                q !== a && n.container.removeClass("mfp-s-" + q), !b && a === "loading" && (b = n.st.tLoading);
                var c = {
                    status: a,
                    text: b
                };
                y("UpdateStatus", c), a = c.status, b = c.text, n.preloader.html(b), n.preloader.find("a").on("click", function(a) {
                    a.stopImmediatePropagation()
                }), n.container.addClass("mfp-s-" + a), q = a
            }
        },
        _checkIfClose: function(b) {
            if (a(b).hasClass(m)) return;
            var c = n.st.closeOnContentClick,
                d = n.st.closeOnBgClick;
            if (c && d) return !0;
            if (!n.content || a(b).hasClass("mfp-close") || n.preloader && b === n.preloader[0]) return !0;
            if (b !== n.content[0] && !a.contains(n.content[0], b)) {
                if (d && a.contains(document, b)) return !0
            } else if (c) return !0;
            return !1
        },
        _addClassToMFP: function(a) {
            n.bgOverlay.addClass(a), n.wrap.addClass(a)
        },
        _removeClassFromMFP: function(a) {
            this.bgOverlay.removeClass(a), n.wrap.removeClass(a)
        },
        _hasScrollBar: function(a) {
            return (n.isIE7 ? s.height() : document.body.scrollHeight) > (a || r.height())
        },
        _setFocus: function() {
            (n.st.focus ? n.content.find(n.st.focus).eq(0) : n.wrap).focus()
        },
        _onFocusIn: function(b) {
            if (b.target !== n.wrap[0] && !a.contains(n.wrap[0], b.target)) return n._setFocus(), !1
        },
        _parseMarkup: function(b, c, d) {
            var e;
            d.data && (c = a.extend(d.data, c)), y(f, [b, c, d]), a.each(c, function(c, d) {
                if (d === undefined || d === !1) return !0;
                e = c.split("_");
                if (e.length > 1) {
                    var f = b.find(j + "-" + e[0]);
                    if (f.length > 0) {
                        var g = e[1];
                        g === "replaceWith" ? f[0] !== d[0] && f.replaceWith(d) : g === "img" ? f.is("img") ? f.attr("src", d) : f.replaceWith(a("<img>").attr("src", d).attr("class", f.attr("class"))) : f.attr(e[1], d)
                    }
                } else b.find(j + "-" + c).html(d)
            })
        },
        _getScrollbarSize: function() {
            if (n.scrollbarSize === undefined) {
                var a = document.createElement("div");
                a.style.cssText = "width: 99px; height: 99px; overflow: scroll; position: absolute; top: -9999px;", document.body.appendChild(a), n.scrollbarSize = a.offsetWidth - a.clientWidth, document.body.removeChild(a)
            }
            return n.scrollbarSize
        }
    }, a.magnificPopup = {
        instance: null,
        proto: o.prototype,
        modules: [],
        open: function(b, c) {
            return A(), b ? b = a.extend(!0, {}, b) : b = {}, b.isObj = !0, b.index = c || 0, this.instance.open(b)
        },
        close: function() {
            return a.magnificPopup.instance && a.magnificPopup.instance.close()
        },
        registerModule: function(b, c) {
            c.options && (a.magnificPopup.defaults[b] = c.options), a.extend(this.proto, c.proto), this.modules.push(b)
        },
        defaults: {
            disableOn: 0,
            key: null,
            midClick: !1,
            mainClass: "",
            preloader: !0,
            focus: "",
            closeOnContentClick: !1,
            closeOnBgClick: !0,
            closeBtnInside: !0,
            showCloseBtn: !0,
            enableEscapeKey: !0,
            modal: !1,
            alignTop: !1,
            removalDelay: 0,
            prependTo: null,
            fixedContentPos: "auto",
            fixedBgPos: "auto",
            overflowY: "auto",
            closeMarkup: '<button title="%title%" type="button" class="mfp-close">&#215;</button>',
            tClose: "Close (Esc)",
            tLoading: "Loading...",
            autoFocusLast: !0
        }
    }, a.fn.magnificPopup = function(b) {
        A();
        var c = a(this);
        if (typeof b == "string")
            if (b === "open") {
                var d, e = p ? c.data("magnificPopup") : c[0].magnificPopup,
                    f = parseInt(arguments[1], 10) || 0;
                e.items ? d = e.items[f] : (d = c, e.delegate && (d = d.find(e.delegate)), d = d.eq(f)), n._openClick({
                    mfpEl: d
                }, c, e)
            } else n.isOpen && n[b].apply(n, Array.prototype.slice.call(arguments, 1));
        else b = a.extend(!0, {}, b), p ? c.data("magnificPopup", b) : c[0].magnificPopup = b, n.addGroup(c, b);
        return c
    };
    var C, D = function() {
        return C === undefined && (C = document.createElement("p").style.MozTransform !== undefined), C
    };
    a.magnificPopup.registerModule("zoom", {
        options: {
            enabled: !1,
            easing: "ease-in-out",
            duration: 300,
            opener: function(a) {
                return a.is("img") ? a : a.find("img")
            }
        },
        proto: {
            initZoom: function() {
                var a = n.st.zoom,
                    d = ".zoom",
                    e;
                if (!a.enabled || !n.supportsTransition) return;
                var f = a.duration,
                    g = function(b) {
                        var c = b.clone().removeAttr("style").removeAttr("class").addClass("mfp-animated-image"),
                            d = "all " + a.duration / 1e3 + "s " + a.easing,
                            e = {
                                position: "fixed",
                                zIndex: 9999,
                                left: 0,
                                top: 0,
                                "-webkit-backface-visibility": "hidden"
                            },
                            f = "transition";
                        return e["-webkit-" + f] = e["-moz-" + f] = e["-o-" + f] = e[f] = d, c.css(e), c
                    },
                    h = function() {
                        n.content.css("visibility", "visible")
                    },
                    i, j;
                w("BuildControls" + d, function() {
                    if (n._allowZoom()) {
                        clearTimeout(i), n.content.css("visibility", "hidden"), e = n._getItemToZoom();
                        if (!e) {
                            h();
                            return
                        }
                        j = g(e), j.css(n._getOffset()), n.wrap.append(j), i = setTimeout(function() {
                            j.css(n._getOffset(!0)), i = setTimeout(function() {
                                h(), setTimeout(function() {
                                    j.remove(), e = j = null, y("ZoomAnimationEnded")
                                }, 16)
                            }, f)
                        }, 16)
                    }
                }), w(c + d, function() {
                    if (n._allowZoom()) {
                        clearTimeout(i), n.st.removalDelay = f;
                        if (!e) {
                            e = n._getItemToZoom();
                            if (!e) return;
                            j = g(e)
                        }
                        j.css(n._getOffset(!0)), n.wrap.append(j), n.content.css("visibility", "hidden"), setTimeout(function() {
                            j.css(n._getOffset())
                        }, 16)
                    }
                }), w(b + d, function() {
                    n._allowZoom() && (h(), j && j.remove(), e = null)
                })
            },
            _allowZoom: function() {
                return n.currItem.type === "image"
            },
            _getItemToZoom: function() {
                return n.currItem.hasSize ? n.currItem.img : !1
            },
            _getOffset: function(b) {
                var c;
                b ? c = n.currItem.img : c = n.st.zoom.opener(n.currItem.el || n.currItem);
                var d = c.offset(),
                    e = parseInt(c.css("padding-top"), 10),
                    f = parseInt(c.css("padding-bottom"), 10);
                d.top -= a(window).scrollTop() - e;
                var g = {
                    width: c.width(),
                    height: (p ? c.innerHeight() : c[0].offsetHeight) - f - e
                };
                return D() ? g["-moz-transform"] = g.transform = "translate(" + d.left + "px," + d.top + "px)" : (g.left = d.left, g.top = d.top), g
            }
        }
    });
    var E = "iframe",
        F = "//about:blank",
        G = function(a) {
            if (n.currTemplate[E]) {
                var b = n.currTemplate[E].find("iframe");
                b.length && (a || (b[0].src = F), n.isIE8 && b.css("display", a ? "block" : "none"))
            }
        };
    a.magnificPopup.registerModule(E, {
        options: {
            markup: '<div class="mfp-iframe-scaler"><div class="mfp-close"></div><iframe class="mfp-iframe" src="//about:blank" frameborder="0" allowfullscreen></iframe></div>',
            srcAction: "iframe_src",
            patterns: {
                youtube: {
                    index: "youtube.com",
                    id: "v=",
                    src: "//www.youtube.com/embed/%id%?autoplay=1"
                },
                vimeo: {
                    index: "vimeo.com/",
                    id: "/",
                    src: "//player.vimeo.com/video/%id%?autoplay=1"
                },
                gmaps: {
                    index: "//maps.google.",
                    src: "%id%&output=embed"
                }
            }
        },
        proto: {
            initIframe: function() {
                n.types.push(E), w("BeforeChange", function(a, b, c) {
                    b !== c && (b === E ? G() : c === E && G(!0))
                }), w(b + "." + E, function() {
                    G()
                })
            },
            getIframe: function(b, c) {
                var d = b.src,
                    e = n.st.iframe;
                a.each(e.patterns, function() {
                    if (d.indexOf(this.index) > -1) return this.id && (typeof this.id == "string" ? d = d.substr(d.lastIndexOf(this.id) + this.id.length, d.length) : d = this.id.call(this, d)), d = this.src.replace("%id%", d), !1
                });
                var f = {};
                return e.srcAction && (f[e.srcAction] = d), n._parseMarkup(c, f, b), n.updateStatus("ready"), c
            }
        }
    });
    var H = function(a) {
            var b = n.items.length;
            return a > b - 1 ? a - b : a < 0 ? b + a : a
        },
        I = function(a, b, c) {
            return a.replace(/%curr%/gi, b + 1).replace(/%total%/gi, c)
        };
    a.magnificPopup.registerModule("gallery", {
        options: {
            enabled: !1,
            arrowMarkup: '<button title="%title%" type="button" class="mfp-arrow mfp-arrow-%dir%"></button>',
            preload: [0, 2],
            navigateByImgClick: !0,
            arrows: !0,
            tPrev: "Previous (Left arrow key)",
            tNext: "Next (Right arrow key)",
            tCounter: "%curr% of %total%"
        },
        proto: {
            initGallery: function() {
                var c = n.st.gallery,
                    d = ".mfp-gallery";
                n.direction = !0;
                if (!c || !c.enabled) return !1;
                u += " mfp-gallery", w(g + d, function() {
                    c.navigateByImgClick && n.wrap.on("click" + d, ".mfp-img", function() {
                        if (n.items.length > 1) return n.next(), !1
                    }), s.on("keydown" + d, function(a) {
                        a.keyCode === 37 ? n.prev() : a.keyCode === 39 && n.next()
                    })
                }), w("UpdateStatus" + d, function(a, b) {
                    b.text && (b.text = I(b.text, n.currItem.index, n.items.length))
                }), w(f + d, function(a, b, d, e) {
                    var f = n.items.length;
                    d.counter = f > 1 ? I(c.tCounter, e.index, f) : ""
                }), w("BuildControls" + d, function() {
                    if (n.items.length > 1 && c.arrows && !n.arrowLeft) {
                        var b = c.arrowMarkup,
                            d = n.arrowLeft = a(b.replace(/%title%/gi, c.tPrev).replace(/%dir%/gi, "left")).addClass(m),
                            e = n.arrowRight = a(b.replace(/%title%/gi, c.tNext).replace(/%dir%/gi, "right")).addClass(m);
                        d.click(function() {
                            n.prev()
                        }), e.click(function() {
                            n.next()
                        }), n.container.append(d.add(e))
                    }
                }), w(h + d, function() {
                    n._preloadTimeout && clearTimeout(n._preloadTimeout), n._preloadTimeout = setTimeout(function() {
                        n.preloadNearbyImages(), n._preloadTimeout = null
                    }, 16)
                }), w(b + d, function() {
                    s.off(d), n.wrap.off("click" + d), n.arrowRight = n.arrowLeft = null
                })
            },
            next: function() {
                n.direction = !0, n.index = H(n.index + 1), n.updateItemHTML()
            },
            prev: function() {
                n.direction = !1, n.index = H(n.index - 1), n.updateItemHTML()
            },
            goTo: function(a) {
                n.direction = a >= n.index, n.index = a, n.updateItemHTML()
            },
            preloadNearbyImages: function() {
                var a = n.st.gallery.preload,
                    b = Math.min(a[0], n.items.length),
                    c = Math.min(a[1], n.items.length),
                    d;
                for (d = 1; d <= (n.direction ? c : b); d++) n._preloadItem(n.index + d);
                for (d = 1; d <= (n.direction ? b : c); d++) n._preloadItem(n.index - d)
            },
            _preloadItem: function(b) {
                b = H(b);
                if (n.items[b].preloaded) return;
                var c = n.items[b];
                c.parsed || (c = n.parseEl(b)), y("LazyLoad", c), c.type === "image" && (c.img = a('<img class="mfp-img" />').on("load.mfploader", function() {
                    c.hasSize = !0
                }).on("error.mfploader", function() {
                    c.hasSize = !0, c.loadError = !0, y("LazyLoadError", c)
                }).attr("src", c.src)), c.preloaded = !0
            }
        }
    }), A()
});

/*--------------------------
YouRam
--------------------------*/
! function(e) {
    "use strict";
    var t = {
            apiKey: "AIzaSyDLlnSIppxQEjiy4Rt5mYJDDHQQI-ynPwQ",
            sourceLink: "https://www.youtube.com/channel/UCqj36njQUT_HCvw6eHzN-hw",
            maxResults: "10",
            videoDisplayMode: "popup",
            defaultSortOrder: "recent-first",
            youramDisplayMode: "grid",
            clearListOnDisplay: !0,
            displayFirstVideoOnLoad: !1,
            minimumViewsPerDayForTrendingVideos: "5",
            themeColor: "rgb(235, 229, 52)",
			//youramBackgroundColor: "#dadbdb",
            itemBackgroundColor: "#fbfbfb",
           // titleColor: "#383838",
           // descriptionColor: "#686868",
            viewsColor: "#6f6f6f",
            controlsTextColor: "black",
            titleFontFamily: "B612",
            generalFontFamily: "B612",
           // titleFontSize: "0.8",
            titleFontWeight: "bold",
           // descriptionFontSize: "0.85",
            viewsDateFontSize: "0.75",
            baseFontSize: "16px",
            responsiveBreakpoints: [600, 850, 1050, 1400],
            gridThumbnailType: "simple",
            dateFormat: "relative",
            loadingMechanism: "load-more",
            loadMoreText: '<i class="fa fa-plus"></i>&nbsp;&nbsp;Load more videos..',
            loadingText: "loading...",
            allDoneText: '<i class="fa fa-times"></i>&nbsp;&nbsp;All done..',
            showFixedPlayIcon: !0,
            iconShape: "circle",
            showHoverAnimation: !0,
            aspectRatio: .5625,
            pageToken: null,
            nextPageToken: null,
            allDoneFlag: !1,
            previousTabId: null,
            popupAlignTop: !1,
            hideLoadingMechanism: !1,
            hideDuration: !1,
            hideThumbnailShadow: !1
        },
        i = function(e) {
            var t = e.data("settings");
            t.nextPageToken = null, t.allDoneFlag = !1, e.data("settings", t)
        },
        o = function(e, t) {
            var i, o, l, a, n, s = {
                identifier: "",
                identifierType: ""
            };
            return t = (t = (t = t.replace(/\/$/, "")).replace("/videos", "")).replace("/playlists", ""), "youtube-channel-uploads" == e || "youtube-channel-playlists" == e ? -1 == (i = t.indexOf("/user/")) ? -1 == (i = t.indexOf("/channel/")) ? (alert("\n\nChannel Link should be of the format: \nhttps://www.youtube.com/channel/UComP_epzeKzvBX156r6pm1Q \nOR\nhttps://www.youtube.com/user/designmilk\n\n"), s.identifierType = "youtube-channel-id", s.identifier = "error") : (o = t.substring(i + 9), s.identifierType = "youtube-channel-id", s.identifier = o) : (l = t.substring(i + 6), s.identifierType = "youtube-channel-user", s.identifier = l) : "youtube-playlist-videos" == e ? (s.identifierType = "youtube-playlist-id", -1 == (i = t.indexOf("list=")) ? (alert("\n\nPlaylist Link should be of the format: \nhttps://www.youtube.com/watch?v=hZi6Jh6s0qU&list=PLpIEeUepLM9xAY-QuGrUwILIMtJfyaAol\n\n"), s.identifier = "error") : (a = t.substring(i + 5), s.identifier = a)) : "vimeo-user-videos" == e && (s.identifierType = "vimeo-user", -1 == (i = t.indexOf("vimeo.com/")) ? (alert("\n\nVimeo User Link should be of the format: \nhttps://vimeo.com/user123\n\n"), s.identifier = "error") : (n = t.substring(i + 10), s.identifier = n)), s
        },
        l = function(t, i, o, l) {
            var n = "",
                s = l.data("settings");
            "youtube-channel-user" == t ? n = "https://www.googleapis.com/youtube/v3/channels?part=contentDetails%2Csnippet&forUsername=" + i + "&key=" + s.apiKey : "youtube-channel-id" == t && (n = "https://www.googleapis.com/youtube/v3/channels?part=contentDetails%2Csnippet&id=" + i + "&key=" + s.apiKey), e.ajax({
                url: n,
                type: "GET",
                async: !0,
                cache: !0,
                dataType: "json",
                success: function(e) {
                    var t, i = e.items[0].contentDetails.relatedPlaylists.uploads,
                        n = e.items[0].id; - 1 != o.indexOf("youtube-channel-uploads") ? t = "youtube-channel-uploads-" + i : -1 != o.indexOf("youtube-channel-playlists") && (t = "youtube-channel-playlists-" + n), a(t, l), h(l)
                },
                error: function(e) {}
            })
        },
        a = function(e, t) {
            var i = t.data("settings");
            i.currentSourceID = e, t.data("settings", i)
        },
        n = function(t, i) {
            var o, l = "",
                a = i.data("settings");
            null != a.nextPageToken && (l = "&pageToken=" + a.nextPageToken), o = "https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&playlistId=" + t + "&maxResults=" + a.maxResults + l + "&key=" + a.apiKey, e.ajax({
                url: o,
                type: "GET",
                async: !0,
                cache: !0,
                dataType: "json",
                success: function(e) {
                    r(e.items, i), s("youtube", e.nextPageToken, i)
                },
                error: function(e) {}
            })
        },
        s = function(e, t, i) {
            var o = i.data("settings");
            if (null == t) return w(i), o.nextPageToken = null, void i.data("settings", o);
            b(i), "youtube" && (o.nextPageToken = t), i.data("settings", o)
        },
        r = function(t, i) {
            for (var o, l = [], a = [], n = 0; n < t.length; n++)(o = new Object).image = t[n].snippet.thumbnails.medium.url, o.title = t[n].snippet.title, o.description = t[n].snippet.description, o.playlistId = t[n].snippet.playlistId, o.videoId = t[n].snippet.resourceId.videoId, o.link = "https://www.youtube.com/watch?v=" + o.videoId + "&list=" + o.playlistId, a.push(o), l.push(o.videoId);
            ! function(t, i, o) {
                var l = "https://www.googleapis.com/youtube/v3/videos?part=statistics%2CcontentDetails%2Csnippet&id=" + t + "&key=" + o.data("settings").apiKey;
                e.ajax({
                    url: l,
                    type: "GET",
                    async: !0,
                    cache: !0,
                    dataType: "json",
                    success: function(e) {
                        i = d(e.items, i, o), c(i, o), u(o)
                    },
                    error: function(e) {}
                })
            }(l, a, i)
        },
        d = function(e, t, i) {
            for (var o, l, a, n = (new Date).getTime(), s = (i.data("settings"), 0); s < e.length; s++) o = e[s].statistics.viewCount, t[s].views = o, t[s].date = e[s].snippet.publishedAt, t[s].formattedDate = D(t[s].date), t[s].likes = e[s].statistics.likeCount, t[s].commaSeparatedLikes = T(e[s].statistics.likeCount), t[s].comments = e[s].statistics.commentCount, t[s].commaSeparatedComments = T(e[s].statistics.commentCount), l = o / ((n - new Date(t[s].date).getTime()) / 1e3 / 60 / 60 / 24), t[s].viewsPerDay = l, o = T(o), t[s].commaSeparatedViews = o, a = O(e[s].contentDetails.duration), t[s].duration = a;
            return t
        },
        c = function(t, i) {
            var o, l, a, n, s, r, d, c, u, p, m, f, h, b, w, x = "",
                C = "",
                k = i.data("settings"),
                T = k.defaultSortOrder,
                D = i.find(".yl-item-container"),
                F = t;
            "popular-first" == T ? F.sort(v) : "recent-first" == T && F.sort(g), k.clearListOnDisplay && y(i);
            for (var O = 0; O < F.length; O++) a = F[O].image, n = F[O].commaSeparatedViews, s = F[O].viewsPerDay, r = F[O].title, d = F[O].description, c = F[O].formattedDate, u = F[O].link, m = "//www.youtube.com/watch?v=" + (p = F[O].videoId), f = F[O].duration, b = F[O].isPlaylist, w = F[O].itemCount, o = "<div class='yl-play-overlay'></div>", l = "<div class='yl-play-overlay-fixed'><div class='yl-play-icon-holder'><i class='fa fa-youtube-play'></i></div></div>", b ? (h = w + " <span>videos</span>", C = "<div class='yl-playlist-video-count-wrapper'><div class='yl-playlist-video-count-box'><span class='yl-playlist-video-count'>" + w + "</span><br>VIDEOS<br><div class='yl-playlist-line-wrapper'><span class='yl-playlist-line'></span><br><span class='yl-playlist-line'></span><br><span class='yl-playlist-line'></span></div></div></div>", o = "", l = "") : h = n + " <span>views</span>", x += "<div class='yl-item-wrapper'>" + ("<div class='yl-item' id='" + p + "' data-likes='" + F[O].commaSeparatedLikes + "' data-comments='" + F[O].commaSeparatedComments + "'><div class='yl-focus' href='" + m + "' data-link='" + u + "'><div class='yl-thumbnail'><img src='" + a + "''>" + (null != f ? "<div class='yl-duration'><i class='fa fa-youtube-play'></i>" + f + "</div>" : "") + o + l + "</div><br/>" + ("<div class='yl-view-bucket' data-views='" + n + "'><div class='yl-view-wrapper'><div class='yl-view-count'>" + h + "</div></div></div>") + "</div><div class='yl-text'><div class='yl-title-description-wrapper'><div class='yl-title'>" + r + "</div><div class='yl-description'>" + (d = I(d)) + "</div></div><div class='yl-separator-for-grid'></div>" + ("<div class='yl-view-string'>" + h + "</div>") + ("<div class='yl-date-bucket'>" + c + "</div>") + (s > k.minimumViewsPerDayForTrendingVideos ? "<div class='yl-views-per-day'><i class='fa fa-bolt'></i></div>" : "") + "</div>" + C + "</div>") + "</div>";
            b ? e("body").addClass("yl-playlist") : e("body").removeClass("yl-playlist"), D.append(x)
        },
        y = function(e) {
            e.find(".yl-item-container").empty()
        },
        u = function(e) {
            var t = e.data("settings");
            "popup" == t.videoDisplayMode ? p(e) : "inline" == t.videoDisplayMode ? m(e) : f(e)
        },
        p = function(e) {
            var t, i = "",
                o = e.data("settings"),
                l = o.autoPlay ? "&autoplay=1" : "&autoplay=0";
            (t = o.currentSourceID).substring(t.indexOf("-", 20) + 1), i = -1 != t.indexOf("vimeo") ? "//player.vimeo.com/video/%id%?badge=0&autopause=0&player_id=0" + l : -1 != t.indexOf("youtube-channel-playlists") ? "//www.youtube.com/embed?listType=playlist&list=%id%&rel=0" + l : "//www.youtube.com/embed/%id%?&rel=0" + l, e.find(".yl-focus").magnificPopup({
                gallery: {
                    enabled: !0
                },
                type: "iframe",
                iframe: {
                    markup: '<div class="mfp-iframe-scaler"><button title="Close (Esc)" type="button" class="mfp-close">×</button><iframe class="mfp-iframe" frameborder="0" allowfullscreen></iframe></div><div class="mfp-preloader">Loading...</div>',
                    patterns: {
                        youtube: {
                            src: i
                        },
                        vimeo: {
                            src: i
                        }
                    }
                },
                alignTop: o.popupAlignTop,
                callbacks: {
                    change: function() {}
                }
            })
        },
        m = function(t) {
            var i = t.data("settings"),
                o = i.currentSourceID;
            o.substring(o.indexOf("-", 20) + 1);
            t.on("click", ".yl-focus", function() {
                var l, a = "",
                    n = e(this).parents(".yl-item").attr("id"),
                    s = t.find(".yl-inline-container"),
                    r = i.autoPlay ? "&autoplay=1" : "&autoplay=0";
                a = -1 != o.indexOf("vimeo") ? "//player.vimeo.com/video/" + n + "?badge=0&autopause=0&player_id=0" + r : -1 != o.indexOf("youtube-channel-playlists") ? "//www.youtube.com/embed?listType=playlist&list=" + n + "&rel=0" + r : "//www.youtube.com/embed/" + n + "?rel=0" + r, l = '<div class="fluid-width-video-wrapper" style="padding-top:' + 100 * i.aspectRatio + '%;"><iframe class="yl-inline-iframe" src="' + a + '" frameborder="0" allowfullscreen></iframe></div>', s.html(l + "").css("display", "inline-block"), e("html, body").animate({
                    scrollTop: s.offset().top - 150
                }, "slow")
            }), i.displayFirstVideoOnLoad && t.find(".yl-focus:first").click()
        },
        f = function(t) {
            t.find(".yl-focus").each(function(t, i) {
                var o = e(i),
                    l = o.data("link");
                o.wrap("<a href='" + l + "' target='_blank'></a>")
            })
        },
        v = function(e, t) {
            return t.views - e.views
        },
        g = function(e, t) {
            return new Date(t.date).getTime() - new Date(e.date).getTime()
        },
        h = function(e) {
            var t = e.data("settings").currentSourceID,
                i = t.substring(t.indexOf("-", 17) + 1); - 1 != t.indexOf("youtube-channel-uploads") ? n(i, e) : -1 != t.indexOf("youtube-channel-playlists") ? getChannelPlaylists(i, e) : -1 != t.indexOf("youtube-playlist-videos") ? n(i, e) : -1 != t.indexOf("vimeo-user-videos") && getVimeoUserVideos(i, e)
        },
        b = function(e) {
            var t, i = e.find(".yl-next-button"),
                o = e.find(".yl-previous-button"),
                l = e.find(".yl-load-more-button");
            t = i.data("text"), i.removeClass("yl-loading").html(t), t = o.data("text"), o.removeClass("yl-loading").html(t), t = l.data("text"), l.removeClass("yl-loading").html(t)
        },
        w = function(e) {
            var t = e.find(".yl-loading"),
                i = e.data("settings");
            null != t && 0 != t.length || (t = "load-more" == i.loadingMechanism ? e.find(".yl-load-more-button") : e.find(".yl-next-button")), t.removeClass("yl-loading").html(i.allDoneText), i.allDoneFlag = !0, e.data("settings", i), e.find(".yl-item").removeClass("yl-fading")
        },
        x = function(e) {
            var t = e.data("settings");
            if (null == t.nextPageToken) return w(e), t.nextPageToken = null, void e.data("settings", t);
            h(e)
        },
        C = function(t) {
            var i = t.data("settings");
            "grid" == i.youramDisplayMode && (t.addClass("yl-grid"), t.width() < i.responsiveBreakpoints[0] ? t.addClass("yl-1-col-grid").removeClass("yl-2-col-grid yl-3-col-grid yl-4-col-grid yl-4-col-grid") : t.width() < i.responsiveBreakpoints[1] ? t.addClass("yl-2-col-grid").removeClass("yl-1-col-grid yl-3-col-grid yl-4-col-grid yl-5-col-grid") : t.width() < i.responsiveBreakpoints[2] ? t.addClass("yl-3-col-grid").removeClass("yl-1-col-grid yl-2-col-grid yl-4-col-grid yl-5-col-grid") : t.width() < i.responsiveBreakpoints[3] ? t.addClass("yl-4-col-grid").removeClass("yl-1-col-grid yl-2-col-grid yl-3-col-grid yl-5-col-grid") : t.addClass("yl-5-col-grid").removeClass("yl-1-col-grid yl-2-col-grid yl-3-col-grid yl-4-col-grid")), "simple" == i.gridThumbnailType ? t.addClass("yl-simple-thumbnails") : "neat" == i.gridThumbnailType ? t.addClass("yl-neat-thumbnails") : t.addClass("yl-full-thumbnails"), "inline" == i.videoDisplayMode && t.width() < 900 ? e("body").addClass("yl-simple-popup") : "popup" == i.videoDisplayMode && e("body").width() < 900 && e("body").addClass("yl-simple-popup")
        },
        k = function(e) {
            e.find(".yl-inline-container").empty(), e.find(".yl-item-container").empty().append(""), e.find(".yl-showing-playlist-name").empty().hide()
        },
        T = function(e) {
            var t = "";
            for (e = "" + e; e.length > 0;) {
                if (!(e.length > 3)) {
                    t = e + t;
                    break
                }
                t = "," + e.substring(e.length - 3) + t, e = e.substring(0, e.length - 3)
            }
            return t
        },
        D = F,
        F = function(e) {
            var t = e.substring(0, e.indexOf("T")).split("-");
            return "<div class='yl-date'>" + t[2] + "</div><div class='yl-month'>" + ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"][t[1] - 1] + "</div><div class='yl-year'>" + t[0] + "</div>"
        },
        O = function(e) {
            var t, i, o = (e = e.replace("PT", "").replace("S", "").replace("M", ":").replace("H", ":")).split(":");
            i = o[0];
            for (var l = 1; l < o.length; l++) i += "" == (t = o[l]) ? ":00" : (t = parseInt(t, 10)) < 10 ? ":0" + t : ":" + t;
            return 1 == o.length && (i = "0:" + i), i
        },
        S = function(e) {
            var t, i, o, l;
            return null == e || "" == e || "undefined" == e ? "?" : (t = Math.abs(new Date - new Date(e)) / 1e3 / 60 / 60) > 24 ? (i = t / 24) > 30 ? (o = i / 30) > 12 ? (l = o / 12, (l = Math.round(l)) <= 1 ? l + " <span>year ago</span>" : l + " <span>years ago</span>") : (o = Math.round(o)) <= 1 ? o + " <span>month ago</span>" : o + " <span>months ago</span>" : (i = Math.round(i)) <= 1 ? i + " <span>day ago</span>" : i + " <span>days ago</span>" : (t = Math.round(t)) < 1 ? "just now" : 1 == t ? t + " <span>hour ago</span>" : t + " <span>hours ago</span>"
        },
        I = function(e) {
            var t, i;
            if (null != (t = (e = e.replace(/"/g, "'")).match(/(http(s)*:\/\/|www\.).+?(\s|\n|$)/g))) {
                for (var o = 0; o < t.length; o++) t[o] = t[o].trim(), e = e.replace(t[o], "~~" + o + "~~");
                for (o = 0; o < t.length; o++) i = 0 == t[o].indexOf("www.") ? "http://" + t[o] : t[o], e = e.replace("~~" + o + "~~", "<a target='_blank' href='" + i + "' class='famax-link'>" + t[o] + "</a>")
            }
            return e
        };
    e.fn.youramSimple = function(n) {
        var s = e.extend({}, t, n);
        return this.data("settings", s),
            function(e) {
                var t = e.data("settings"),
                    i = "<div class='yl-load-more-button' data-text='" + t.loadMoreText + "'>" + t.loadMoreText + "</div>",
                    o = "";
                "load-more" == t.loadingMechanism && (o = i), e.empty().append("<div class='yl-font-controller'><div class='yl-wrapper'><div class='yl-inline-container'></div><div class='yl-item-container'></div><br>" + o + "</div></div>"), k(e)
            }(this),
            function(t) {
                var o, l, a = "",
                    n = t.data("settings"),
                    s = t.attr("id");
                i(t), n.minimumViewsPerDayForTrendingVideos = parseInt(n.minimumViewsPerDayForTrendingVideos, 10), "relative" == n.dateFormat ? D = S : "specific" == n.dateFormat && (D = F), C(t), a += "#" + s + ".youram-simple {background-color: " + n.youramBackgroundColor + ";}", a += "#" + s + " .yl-load-more-button:hover {background: " + n.youramBackgroundColor + ";}", a += "#" + s + " .yl-list-title select, #" + s + " .yl-item, #" + s + " .yl-load-more-button, #" + s + " .yl-previous-button, #" + s + " .yl-next-button {background-color: " + n.itemBackgroundColor + ";}", a += "#" + s + " .yl-view-bucket {color: " + n.viewsColor + "; border-color: " + n.viewsColor + ";}", a += "#" + s + " .yl-date-bucket{color: " + n.viewsColor + ";}", a += "#" + s + " .yl-view-string{color: " + n.viewsColor + ";}", a += "#" + s + " .yl-selected-tab:after{background-color:" + n.viewsColor + ";}", a += "#" + s + " .yl-description, #" + s + " .yl-item, #" + s + " .yl-loader, #" + s + " .yl-list-title {color:" + n.descriptionColor + ";}", a += "#" + s + " .yl-view-bucket-seen {background-color: " + n.themeColor + ";border-color: " + n.themeColor + "; color:white;}", a += "#" + s + " .yl-grid .yl-view-bucket-seen {color: " + n.themeColor + "; background-color:inherit;}", a += "#" + s + " .yl-loader {border-color: " + n.themeColor + ";}", a += "#" + s + " .yl-load-more-button, #" + s + " .yl-previous-button {}", a += "#" + s + " .yl-list-title select{box-shadow: 0 0px 2px rgba(0, 0, 0, 0.2), -0.2em 0px 0px 0px " + n.themeColor + ";}", a += "#" + s + " .yl-header, #" + s + " .yl-cta-button, #" + s + " .yl-switch, #" + s + " .yl-showing-playlist-name{background-color:" + n.themeColor + "; color:" + n.headerTextColor + ";}", a += "#" + s + " .yl-description a, .yp-popup-description a, .yp-comment span {color: " + n.themeColor + ";}", a += ".yp-share:hover, .yp-post-likes:hover, .yp-add-comment-button:hover {background-color: " + n.themeColor + "; box-shadow: 0px 0px 0px 1px " + n.themeColor + ";}", a += "#" + s + " .yl-play-fill-color{background-color:" + n.themeColor + ";}", a += "#" + s + " .yl-title {color: " + n.titleColor + ";}", a += "#" + s + " .yl-list-title select, #" + s + " .yl-load-more-button, #" + s + " .yl-previous-button, #" + s + " .yl-next-button {color:" + n.controlsTextColor + ";}", a += "#" + s + " .yl-tab-container{color: " + n.tabsColor + ";}", -1 != (o = n.themeColor).indexOf("rgb") && (a += ".yl-views-per-day{border-color: " + (l = (l = o.substring(0, o.length - 1) + ",0.1)").replace("rgb", "rgba")) + ";}", a += "#" + s + " .yl-load-more-button:hover {background: " + l + ";}", a += "#" + s + " .yl-loading {}"), a += ".yl-title {font-size:" + n.titleFontSize + "em !important; font-weight:" + n.titleFontWeight + " !important;}", a += ".yl-description {font-size:" + n.descriptionFontSize + "em !important;}", a += ".yl-date-bucket,.yl-view-string {font-size:" + n.viewsDateFontSize + "em !important;}", a += ".youram-simple,.mfp-container{font-size: " + n.baseFontSize + ";}", a += ".yl-item,.yl-callout,.yl-offer,.yl-load-more-button,.yl-duration{font-family:" + n.generalFontFamily + ";}", a += ".yl-title,.yl-offer-title,.yl-callout-title {font-family:" + n.titleFontFamily + ";}", n.hideLoadingMechanism && (a += "#" + s + " .yl-load-more-button, #" + s + " .yl-previous-button, #" + s + " .yl-next-button{display:none;}", a += "#" + s + " .yl-cta-button{width:100%;}"), a += "#" + s + "  #" + s + " .yl-previous-button, #" + s + " .yl-next-button {width: 48.5%;}", n.hideDuration && (a += "#" + s + " .yl-duration {display:none;}"), n.hideThumbnailShadow && (a += "#" + s + " .yl-item {box-shadow:none;}"), e(".youram-added-styles-" + s).remove(), e("body").append("<style class='youram-added-styles-" + s + "'>" + a + "</style>")
            }(this),
            function(e) {
                var t, i, n, s;
                e.find(".yl-tab-container");
                i = "error", i = -1 != (n = e.data("settings").sourceLink).indexOf("&list=") ? "youtube-playlist-videos" : "youtube-channel-uploads", "error" != (t = o(i, n)).identifier && (s = i + "-" + t.identifier, "youtube-channel-uploads" == i ? l(t.identifierType, t.identifier, s, e) : "youtube-channel-playlists" == i && "youtube-channel-user" == t.identifierType ? l(t.identifierType, t.identifier, s, e) : (a(s, e), h(e)))
            }(this),
            function(t) {
                e("body").on("click", ".yp-popup-more-button", function() {
                    e(this).remove(), e(".yp-show-less").removeClass("yp-show-less")
                }), e(document).on("click", ".yp-post-likes", function() {
                    handleYouTubeLikes(t)
                }), e(document).on("click", ".yp-add-comment-button", function() {
                    handleYouTubeComments(t)
                })
            }(this),
            function(t) {
                var i, o = t.data("settings"),
                    l = t.attr("id");
                o.showFixedPlayIcon ? (i = "#" + l + " .yl-duration i{}", i += "#" + l + " .yl-play-overlay-fixed {display: block;}", "circle" == o.iconShape && (i += "#" + l + " .yl-play-icon-holder {width: 2.6em;height: 2.6em;border-radius: 100%;padding: 0.1em 0.25em;box-sizing: border-box;}"), e("body").append("<style class='youram-added-styles-" + l + "'>" + i + "</style>"), o.showHoverAnimation && (t.on("mouseenter", ".yl-focus", function() {
                    e(this).find(".yl-play-icon-holder").addClass("yl-play-fill-color")
                }), t.on("mouseleave", ".yl-focus", function() {
                    e(this).find(".yl-play-icon-holder").removeClass("yl-play-fill-color")
                }))) : o.showHoverAnimation && (t.on("mouseenter", ".yl-focus", function() {
                    e(this).find(".yl-duration").addClass("yl-duration-big"), e(this).find(".yl-play-overlay").show()
                }), t.on("mouseleave", ".yl-focus", function() {
                    e(this).find(".yl-duration").removeClass("yl-duration-big"), e(this).find(".yl-play-overlay").hide()
                }))
            }(this),
            function(t) {
                var i = t.data("settings");
                t.on("click", ".yl-load-more-button", function() {
                    i.displayFirstVideoOnLoad = !1, i.allDoneFlag || (e(this).addClass("yl-loading").html(i.loadingText), i.clearListOnDisplay = !1, t.data("settings", i), x(t))
                })
            }(this),
            function(t) {
                e(window).resize(function() {
                    C(t)
                })
            }(this), this
    }
}(jQuery);