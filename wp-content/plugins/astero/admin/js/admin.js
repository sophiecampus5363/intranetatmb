/**
 * Custom jQuery for Custom Metaboxes and Fields
 */
jQuery(document).ready( function($) {
        /**
         * Delete media
         */
        jQuery('.cmb-container').on( 'click', '.delete-media', function() {
                
                var button = $(this),
                    attach_id = button.attr('id').replace('delete-',''),
                    media = $('#' + attach_id),
                    input_id = button.data('input-name');
                    
                media.addClass('loading');
                (button.parent().parent().parent()).find('.uploader .cmb-button').show();
                media.remove();
                $('#' + input_id).val('');
        });
        
        /**
         * Init color picker
         */
        jQuery('.colorpicker').wpColorPicker();
        
        /**
         * Init tabs
         */
        jQuery( "#tabs" ).tabs().addClass( "ui-tabs-vertical ui-helper-clearfix" );
        jQuery( "#tabs li" ).removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );
        
        /**
         * Open media uploader
         */
        jQuery('.uploader').on('click', '.upload_image_button', function(e) {
                e.preventDefault();
                
                var button = $(this),
                    upload_id = button.attr('id'),
                    lib = button.attr('data-lib'),
                    input_id = button.attr('data-input-name');

                var custom_uploader = wp.media({
                        title: cmb_vars.mediaTitle,
                        button: {
                                text: cmb_vars.mediaButton
                        },
                        library: {
                                type: lib
                        },
                        frame: 'select',
                        multiple: false  // Set this to true to allow multiple files to be selected
                }).on('select', function() {
                        var selections = custom_uploader.state().get('selection');
                        selections.each(function(selection) {
                                attachment = selection.toJSON();
                                
                                if ( lib == 'image' ) {
                                        var size = (typeof (attachment.sizes["thumbnail"]) == 'undefined') ? attachment : attachment.sizes["thumbnail"],
                                            btn_parent = button.parent().parent().parent();

                                        btn_parent.find(".image-preview").append('<li id="' + upload_id + attachment.id + '">'
                                                                 + '<img src="' + size.url +'">'
                                                                 + '<a href="javascript:;" id="delete-' + upload_id + attachment.id + '" class="cmb-button small-button delete-media" data-input-name="' + input_id + '">&times;</a>'
                                                                 + '</li>');
                                        jQuery('#' + input_id).val(attachment.id);
                                        button.hide();
                                } else {
                                        button.after('<div id="' + upload_id + attachment.id + '"><span class="description">' + attachment.url + '</span><a href="javascript:;" id="delete-' + upload_id + attachment.id + '" class="cmb-button small-button delete-media" data-input-name="' + input_id + '">&times;</a></div>');
                                        button.parent().find('input').val(attachment.id);
                                        button.hide();
                                }
                        });
                })
                .open();
        });
        
        jQuery('.select-change').change(function() {
                var self = $(this),
                    id = self.attr('id');
                
                if ( self.attr('type') == 'checkbox' ) {
                        var value = self.is(':checked') ? '1' : '0';
                } else {
                        var value = self.val();
                }
                
                jQuery('*[data-check-field="' + id + '"]').hide();

                if ( value.length > 0 ) {
                        jQuery('*[data-check-value*="' + id + '-' + value + '"]').show();  
                }
        });
        
        jQuery('#font_family').change( function() {
                var self = jQuery(this).find(':selected'),
                    variants = self.data('variants'),
                    $variants = jQuery('#font_variant').empty(),
                    subsets = self.data('subsets'),
                    $subsets = jQuery('#font_subset').empty(),
                    backup = self.data('backup'),
                    $backup = jQuery('#font_backup');

                update_select($variants, variants);
                update_select($subsets, subsets);
                $backup.val('');
                
                if ( typeof backup != 'undefined' && backup.length > 0 ) {
                        $backup.val(backup);
                }
                
                function update_select(el, data) {
                        el.hide();
                        
                        if ( typeof data != 'undefined' && data.length > 0 ) {
                                data = data.split(',');
                                
                                for( var i = 0; i < data.length; i++ ) {
                                        el.append('<option value="' + data[i] + '">' + data[i] + '</option>');
                                }
                                el.show();
                        }
                }
        });
        
        jQuery('.select-radio').click(function() {
                $(this).find('input[type="radio"]').attr('checked', 'checked');    
        });
        
        jQuery('.js-hide').hide();
});
