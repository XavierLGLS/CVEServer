// /*
//  * Extend Leaflet's Marker to allow setting a rotation in degrees.
//  *
//  * Based on comments by @runanet and @coomsie 
//  * https://github.com/Leaflet/Leaflet/issues/386
//  *
//  * Wrapping function is needed to preserve L.Marker.update function
//  */
// (function () {
//     var _old__setPos = L.Marker.prototype._setPos;
//     L.Marker.include({
//         _updateImg: function(i, a, s) {
//             a = L.point(s).divideBy(2)._subtract(L.point(a));
//             var transform = '';
//             transform += ' translate(' + -a.x + 'px, ' + -a.y + 'px)';
//             transform += ' rotate(' + this.options.iconAngle + 'deg)';
//             transform += ' translate(' + a.x + 'px, ' + a.y + 'px)';
//             i.style[L.DomUtil.TRANSFORM] += transform;
//         },

//         setIconAngle: function (iconAngle) {
//             this.options.iconAngle = iconAngle;

//             if (this._map) this.update();
//         },

//         _setPos: function (pos) {
//             if (this._icon) {
//                 this._icon.style[L.DomUtil.TRANSFORM] = "";
//             }
//             if (this._shadow) {
//                 this._shadow.style[L.DomUtil.TRANSFORM] = "";
//             }

//             _old__setPos.apply(this,[pos]);

//             if (this.options.iconAngle) {
//                 var a = this.options.icon.options.iconAnchor;
//                 var s = this.options.icon.options.iconSize;
//                 var i;
//                 if (this._icon) {
//                     i = this._icon;
//                     this._updateImg(i, a, s);
//                 }

//             }
//         }
//     });
// }());



(function() {

    // save these original methods before they are overwritten

    var proto_initIcon = L.Marker.prototype._initIcon;

    var proto_setPos = L.Marker.prototype._setPos;



    var oldIE = (L.DomUtil.TRANSFORM === 'msTransform');



    L.Marker.addInitHook(function () {

        var iconOptions = this.options.icon && this.options.icon.options;

        var iconAnchor = iconOptions && this.options.icon.options.iconAnchor;

        if (iconAnchor) {

            iconAnchor = (iconAnchor[0] + 'px ' + iconAnchor[1] + 'px');

        }

        this.options.rotationOrigin = this.options.rotationOrigin || iconAnchor || 'center bottom' ;

        this.options.rotationAngle = this.options.rotationAngle || 0;



        // Ensure marker keeps rotated during dragging

        this.on('drag', function(e) { e.target._applyRotation(); });

    });



    L.Marker.include({

        _initIcon: function() {

            proto_initIcon.call(this);

        },



        _setPos: function (pos) {

            proto_setPos.call(this, pos);

            this._applyRotation();

        },



        _applyRotation: function () {

            if(this.options.rotationAngle) {

                this._icon.style[L.DomUtil.TRANSFORM+'Origin'] = this.options.rotationOrigin;



                if(oldIE) {

                    // for IE 9, use the 2D rotation

                    this._icon.style[L.DomUtil.TRANSFORM] = 'rotate(' + this.options.rotationAngle + 'deg)';

                } else {

                    // for modern browsers, prefer the 3D accelerated version

                    this._icon.style[L.DomUtil.TRANSFORM] += ' rotateZ(' + this.options.rotationAngle + 'deg)';

                }

            }

        },



        setRotationAngle: function(angle) {

            this.options.rotationAngle = angle;

            this.update();

            return this;

        },



        setRotationOrigin: function(origin) {

            this.options.rotationOrigin = origin;

            this.update();

            return this;

        }

    });

})();