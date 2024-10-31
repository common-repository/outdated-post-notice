(function ( $ ) {
	"use strict";

    $(function () {
        var $colorInput = $('.fs-color-picker'),
            $previewDiv = $('.outdated-post-notice'),
            $borderColorInput = $('.fs-border-color'),
            $noticeCheckbox   = $('.outdated-post-notice-enabled'),
            $noticeInput      = $('.outdated-post-notice-message'),
            $noticeDays       = $('.outdated-post-notice-days'),
            $noticePreview    = $('.outdated-post-notice-preview');

        var LightenDarkenColor = function( col, amt ) {
            var usePound = false;

            if ( col[0] == "#" ) {
                col = col.slice(1);
                usePound = true;
            }

            var num = parseInt( col, 16 ),
                r = ( num >> 16 ) + amt;

            if( r > 255 ) {
                r = 255;
            } else if( r < 0 ) {
                r = 0;
            }

            var b = ( ( num >> 8 ) & 0x00FF ) + amt;

            if( b > 255 ) {
                b = 255;
            } else if( b < 0 ) {
                b = 0;
            }

            var g = ( num & 0x0000FF ) + amt;

            if( g > 255 ) {
                g = 255;
            } else if( g < 0 ) {
                g = 0;
            }

            return ( usePound ? "#" : "" ) + ( g | ( b << 8 ) | ( r << 16 ) ).toString(16);
        };

        var updatePreviewDiv = function( val, el ) {
            var borderColor = LightenDarkenColor( val, -20 );

            switch( el ) {

                case 'bg-color':
                    $previewDiv.css( 'background', val );
                    $previewDiv.css( 'border', '1px solid ' + borderColor );
                    $borderColorInput.val( LightenDarkenColor( borderColor, -20 ) );
                    break;

                case 'text-color':
                    $previewDiv.css( 'color', val );
                    break;
            }
        };

        var updateNoticePreview = function( obj ) {
            var _output = obj.val();

            if( $.trim( _output ).length === 0 ) {
                $noticePreview.html( _defaultMsg );
            } else {

                $.each( fs_outdated_post_notice_var, function( key, val ) {
                    _output = _output.replace( key, val );
                });

                $noticePreview.html( _output );
            }
        };

        if( $colorInput.length ) {

            $colorInput.each( function() {
                var $this = $(this);
                updatePreviewDiv( $this.val(), $this.data('el') );
            });

            $colorInput.iris({
                change: function( event, ui ) {
                    updatePreviewDiv( ui.color.toString(), $(this).data('el') );
                }
            });

            $(document).on( 'click', function (e) {
                if( ! $( e.target ).is( '.fs-color-picker, .iris-picker, .iris-picker-inner' ) ) {
                    $colorInput.iris('hide');
                }
            });

            $colorInput.on( 'click', function (e) {
                $colorInput.iris('hide');
                $(this).iris('show');
                e.preventDefault();
            });
        }

        if( $noticeInput.length ) {

            var _defaultMsg = 'Please write your notice first.';

            $noticeCheckbox.on( 'change', function (e) {
                if( $(this).is(':checked') ) {
                    $noticeInput.prop( 'readonly', false );
                    $noticeDays.prop( 'readonly', false );
                } else {
                    $noticeInput.prop( 'readonly', true );
                    $noticeDays.prop( 'readonly', true );
                }
            });

            updateNoticePreview( $noticeInput );
            $noticeInput.on( 'keyup', function() {
                updateNoticePreview( $noticeInput );
            });
        }

	});

})(jQuery);