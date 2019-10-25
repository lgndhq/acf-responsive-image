<?php

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


// check if class already exists
if( !class_exists('lgnd_acf_field_responsive_image') ) :


class lgnd_acf_field_responsive_image extends acf_field {
	function __construct( $settings ) {

		/*
		*  name (string) Single word, no spaces. Underscores allowed
		*/

		$this->name = 'responsive_image';


		/*
		*  label (string) Multiple words, can include spaces, visible when selecting a field type
		*/

		$this->label = __('Responsive Image', 'lgndacf');


		/*
		*  category (string) basic | content | choice | relational | jquery | layout | CUSTOM GROUP NAME
		*/

		$this->category = 'content';

		$this->defaults = array(
			'return_format'	=> 'array',
			'preview_size'	=> 'medium',
			'library'		=> 'all',
			'min_width'		=> 0,
			'min_height'	=> 0,
			'min_size'		=> 0,
			'max_width'		=> 0,
			'max_height'	=> 0,
			'max_size'		=> 0,
			'mime_types'	=> '',
			'image_sizes' => '',
		);

		/*
		*  l10n (array) Array of strings that are used in JavaScript. This allows JS strings to be translated in PHP and loaded via:
		*  var message = acf._e('FIELD_NAME', 'error');
		*/

		$this->l10n = array(
			'error'	=> __('Error! Please enter a higher value', 'lgndacf'),
		);


		/*
		*  settings (array) Store plugin settings (url, path, version) as a reference for later use with assets
		*/

		$this->settings = $settings;
    	parent::__construct();

	}


	/*
	*  render_field_settings()
	*
	*  Create extra settings for your field. These are visible when editing a field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field (array) the $field being edited
	*  @return	n/a
	*/

	function render_field_settings( $field ) {
		$clear = array(
			'min_width',
			'min_height',
			'min_size',
			'max_width',
			'max_height',
			'max_size'
		);

		foreach( $clear as $k ) {

			if( empty($field[$k]) ) {

				$field[$k] = '';

			}

		}

		acf_render_field_setting( $field, [
			'label' => __('Image Sizes', 'lgndacf'),
			'instructions' => 'Enter image widths to generate followed by their srcset size (e.g. 1x, 2x, 320w, etc)',
			'type' => 'textarea',
			'name' => 'image_sizes',
			'placeholder' => "320 1x\n640 2x"
		]);

		acf_render_field_setting( $field, array(
			'label'			=> __('Preview Size','lgndacf'),
			'instructions'	=> '',
			'type'			=> 'select',
			'name'			=> 'preview_size',
			'choices'		=> acf_get_image_sizes()
		));


		// library
		acf_render_field_setting( $field, array(
			'label'			=> __('Library','lgndacf'),
			'instructions'	=> __('Limit the media library choice','lgndacf'),
			'type'			=> 'radio',
			'name'			=> 'library',
			'layout'		=> 'horizontal',
			'choices' 		=> array(
				'all'			=> __('All', 'lgndacf'),
				'uploadedTo'	=> __('Uploaded to post', 'lgndacf')
			)
		));


		// min
		acf_render_field_setting( $field, array(
			'label'			=> __('Minimum','lgndacf'),
			'instructions'	=> __('Restrict which images can be uploaded','lgndacf'),
			'type'			=> 'text',
			'name'			=> 'min_width',
			'prepend'		=> __('Width', 'lgndacf'),
			'append'		=> 'px',
		));

		acf_render_field_setting( $field, array(
			'label'			=> '',
			'type'			=> 'text',
			'name'			=> 'min_height',
			'prepend'		=> __('Height', 'lgndacf'),
			'append'		=> 'px',
			'_append' 		=> 'min_width'
		));

		acf_render_field_setting( $field, array(
			'label'			=> '',
			'type'			=> 'text',
			'name'			=> 'min_size',
			'prepend'		=> __('File size', 'lgndacf'),
			'append'		=> 'MB',
			'_append' 		=> 'min_width'
		));


		// max
		acf_render_field_setting( $field, array(
			'label'			=> __('Maximum','lgndacf'),
			'instructions'	=> __('Restrict which images can be uploaded','lgndacf'),
			'type'			=> 'text',
			'name'			=> 'max_width',
			'prepend'		=> __('Width', 'lgndacf'),
			'append'		=> 'px',
		));

		acf_render_field_setting( $field, array(
			'label'			=> '',
			'type'			=> 'text',
			'name'			=> 'max_height',
			'prepend'		=> __('Height', 'lgndacf'),
			'append'		=> 'px',
			'_append' 		=> 'max_width'
		));

		acf_render_field_setting( $field, array(
			'label'			=> '',
			'type'			=> 'text',
			'name'			=> 'max_size',
			'prepend'		=> __('File size', 'lgndacf'),
			'append'		=> 'MB',
			'_append' 		=> 'max_width'
		));


		// allowed type
		acf_render_field_setting( $field, array(
			'label'			=> __('Allowed file types','lgndacf'),
			'instructions'	=> __('Comma separated list. Leave blank for all types','lgndacf'),
			'type'			=> 'text',
			'name'			=> 'mime_types',
		));

	}



	/*
	*  render_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field (array) the $field being rendered
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field (array) the $field being edited
	*  @return	n/a
	*/

	function render_field( $field ) {

		// vars
		$uploader = acf_get_setting('uploader');


		// enqueue
		if( $uploader == 'wp' ) {
			acf_enqueue_uploader();
		}


		// vars
		$url = '';
		$alt = '';
		$div = array(
			'class'					=> 'acf-image-uploader',
			'data-preview_size'		=> $field['preview_size'],
			'data-library'			=> $field['library'],
			'data-mime_types'		=> $field['mime_types'],
			'data-uploader'			=> $uploader
		);


		// has value?
		if( $field['value'] ) {

			// update vars
			$url = wp_get_attachment_image_src($field['value'], $field['preview_size']);
			$alt = get_post_meta($field['value'], '_wp_attachment_image_alt', true);

			// url exists
			if( $url ) $url = $url[0];


			// url exists
			if( $url ) {
				$div['class'] .= ' has-value';
			}

		}

		// get size of preview value
		$size = acf_get_image_size($field['preview_size']);
?>
<div <?php acf_esc_attr_e( $div ); ?>>
	<?php acf_hidden_input(array( 'name' => $field['name'], 'value' => $field['value'] )); ?>
	<div class="show-if-value image-wrap" <?php if( $size['width'] ): ?>style="<?php echo esc_attr('max-width: '.$size['width'].'px'); ?>"<?php endif; ?>>
		<img data-name="image" src="<?php echo esc_url($url); ?>" alt="<?php echo esc_attr($alt); ?>"/>
		<div class="acf-actions -hover">
			<?php
			if( $uploader != 'basic' ):
			?><a class="acf-icon -pencil dark" data-name="edit" href="#" title="<?php _e('Edit', 'acf'); ?>"></a><?php
			endif;
			?><a class="acf-icon -cancel dark" data-name="remove" href="#" title="<?php _e('Remove', 'acf'); ?>"></a>
		</div>
	</div>
	<div class="hide-if-value">
		<?php if( $uploader == 'basic' ): ?>

			<?php if( $field['value'] && !is_numeric($field['value']) ): ?>
				<div class="acf-error-message"><p><?php echo acf_esc_html($field['value']); ?></p></div>
			<?php endif; ?>

			<label class="acf-basic-uploader">
				<?php acf_file_input(array( 'name' => $field['name'], 'id' => $field['id'] )); ?>
			</label>

		<?php else: ?>

			<p><?php _e('No image selected','acf'); ?> <a data-name="add" class="acf-button button" href="#"><?php _e('Add Image','acf'); ?></a></p>

		<?php endif; ?>
	</div>
</div>
<?php

	}


	/*
	*  input_admin_enqueue_scripts()
	*
	*  This action is called in the admin_enqueue_scripts action on the edit screen where your field is created.
	*  Use this action to add CSS + JavaScript to assist your render_field() action.
	*
	*  @type	action (admin_enqueue_scripts)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	function input_admin_enqueue_scripts() {

		// vars
		$url = $this->settings['url'];
		$version = $this->settings['version'];


		// register & include JS
		wp_register_script('lgndacf', "{$url}assets/js/input.js", array('acf-input'), $version);
		wp_enqueue_script('lgndacf');


		// register & include CSS
		//wp_register_style('lgndacf', "{$url}assets/css/input.css", array('acf-input'), $version);
		//wp_enqueue_style('lgndacf');

	}


	/*
	*  input_admin_head()
	*
	*  This action is called in the admin_head action on the edit screen where your field is created.
	*  Use this action to add CSS and JavaScript to assist your render_field() action.
	*
	*  @type	action (admin_head)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/


	function input_admin_head() {

?>
<script type="text/javascript">
(function($, undefined){
	var Field = acf.models.ImageField.extend({

		type: 'responsive_image',

		$control: function(){
			return this.$('.acf-image-uploader');
		},

		$input: function(){
			return this.$('input[type="hidden"]');
		},

		validateAttachment: function( attachment ){

			// defaults
			attachment = attachment || {};

			// WP attachment
			if( attachment.id !== undefined ) {
				attachment = attachment.attributes;
			}

			// args
			attachment = acf.parseArgs(attachment, {
				url: '',
				alt: '',
				title: '',
				filename: '',
				filesizeHumanReadable: '',
				icon: '/wp-includes/images/media/default.png'
			});
			// return
			return attachment;
		},

		render: function( attachment ){
			// vars
			attachment = this.validateAttachment( attachment );

			// update image
		 	this.$('img').attr({
			 	src: attachment.icon,
			 	alt: attachment.alt,
			 	title: attachment.title
		 	});

		 	// update elements
		 	this.$('[data-name="title"]').text( attachment.title );
		 	this.$('[data-name="filename"]').text( attachment.filename ).attr( 'href', attachment.url );
		 	this.$('[data-name="filesize"]').text( attachment.filesizeHumanReadable );

			// vars
			var val = attachment.id || '';

			// update val
		 	acf.val( this.$input(), val );

		 	// update class
		 	if( val ) {
			 	this.$control().addClass('has-value');
		 	} else {
			 	this.$control().removeClass('has-value');
		 	}
		},

		selectAttachment: function(){

			// vars
			var parent = this.parent();
			var multiple = (parent && parent.get('type') === 'repeater');

			// new frame
			var frame = acf.newMediaPopup({
				mode:			'select',
				title:			acf.__('Select File'),
				field:			this.get('key'),
				multiple:		multiple,
				library:		this.get('library'),
				allowedTypes:	this.get('mime_types'),
				select:			$.proxy(function( attachment, i ) {
					if( i > 0 ) {
						this.append( attachment, parent );
					} else {
						this.render( attachment );
					}
				}, this)
			});
		},

		editAttachment: function(){

			// vars
			var val = this.val();

			// bail early if no val
			if( !val ) {
				return false;
			}

			// popup
			var frame = acf.newMediaPopup({
				mode:		'edit',
				title:		acf.__('Edit File'),
				button:		acf.__('Update File'),
				attachment:	val,
				field:		this.get('key'),
				select:		$.proxy(function( attachment, i ) {
					this.render( attachment );
				}, this)
			});
		}
	});

	acf.registerFieldType( Field );
})(jQuery);
</script>
<?php

}



	/*
   	*  input_form_data()
   	*
   	*  This function is called once on the 'input' page between the head and footer
   	*  There are 2 situations where ACF did not load during the 'acf/input_admin_enqueue_scripts' and
   	*  'acf/input_admin_head' actions because ACF did not know it was going to be used. These situations are
   	*  seen on comments / user edit forms on the front end. This function will always be called, and includes
   	*  $args that related to the current screen such as $args['post_id']
   	*
   	*  @type	function
   	*  @date	6/03/2014
   	*  @since	5.0.0
   	*
   	*  @param	$args (array)
   	*  @return	n/a
   	*/

   	/*

   	function input_form_data( $args ) {



   	}

   	*/


	/*
	*  input_admin_footer()
	*
	*  This action is called in the admin_footer action on the edit screen where your field is created.
	*  Use this action to add CSS and JavaScript to assist your render_field() action.
	*
	*  @type	action (admin_footer)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	/*

	function input_admin_footer() {



	}

	*/


	/*
	*  field_group_admin_enqueue_scripts()
	*
	*  This action is called in the admin_enqueue_scripts action on the edit screen where your field is edited.
	*  Use this action to add CSS + JavaScript to assist your render_field_options() action.
	*
	*  @type	action (admin_enqueue_scripts)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	/*

	function field_group_admin_enqueue_scripts() {

	}

	*/


	/*
	*  field_group_admin_head()
	*
	*  This action is called in the admin_head action on the edit screen where your field is edited.
	*  Use this action to add CSS and JavaScript to assist your render_field_options() action.
	*
	*  @type	action (admin_head)
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	n/a
	*  @return	n/a
	*/

	/*

	function field_group_admin_head() {

	}

	*/


	/*
	*  load_value()
	*
	*  This filter is applied to the $value after it is loaded from the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value found in the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*  @return	$value
	*/

	function load_value( $value, $post_id, $field ) {

		if (!is_numeric($value)) return $value;

		$metadata = wp_get_attachment_metadata($value);
		$data = [
			'attachment_id' => $value,
			'srcset' => []
		];
		$base = wp_get_upload_dir()['baseurl'] . '/' . str_replace(basename($metadata['file']), '', $metadata['file']);
		foreach ($metadata['sizes'] as $size => $details) {
			if (stristr($size, $field['key']) !== false) {
				$data['srcset'][] = $base . $details['file'] . ' ' . str_replace($field['key'].'-', '', $size);
			}
		}
		$data['srcset'] = implode(', ', $data['srcset']);
		$data['src'] = wp_get_upload_dir()['baseurl'] . '/' . $metadata['file'];

		if ($field['return_format'] == 'array') {
			return $data;
		} else {
			return sprintf('<img src="%s" srcset="%s">', $data['src'], $data['srcset']);
		}

		// return
		return $data;

	}


	/*
	*  update_value()
	*
	*  This filter is applied to the $value before it is saved in the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value found in the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*  @return	$value
	*/

	/*

	function update_value( $value, $post_id, $field ) {

		return $value;

	}

	*/


	/*
	*  format_value()
	*
	*  This filter is appied to the $value after it is loaded from the db and before it is returned to the template
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value which was loaded from the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*
	*  @return	$value (mixed) the modified value
	*/

	function format_value( $value, $post_id, $field ) {

		// bail early if no value
		if( empty($value) ) {

			return $value;

		}

		return $value;
	}


	/*
	*  validate_value()
	*
	*  This filter is used to perform validation on the value prior to saving.
	*  All values are validated regardless of the field's required setting. This allows you to validate and return
	*  messages to the user if the value is not correct
	*
	*  @type	filter
	*  @date	11/02/2014
	*  @since	5.0.0
	*
	*  @param	$valid (boolean) validation status based on the value and the field's required setting
	*  @param	$value (mixed) the $_POST value
	*  @param	$field (array) the field array holding all the field options
	*  @param	$input (string) the corresponding input name for $_POST value
	*  @return	$valid
	*/

	/*

	function validate_value( $valid, $value, $field, $input ){

		// Basic usage
		if( $value < $field['custom_minimum_setting'] )
		{
			$valid = false;
		}


		// Advanced usage
		if( $value < $field['custom_minimum_setting'] )
		{
			$valid = __('The value is too little!','lgndacf'),
		}


		// return
		return $valid;

	}

	*/


	/*
	*  delete_value()
	*
	*  This action is fired after a value has been deleted from the db.
	*  Please note that saving a blank value is treated as an update, not a delete
	*
	*  @type	action
	*  @date	6/03/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (mixed) the $post_id from which the value was deleted
	*  @param	$key (string) the $meta_key which the value was deleted
	*  @return	n/a
	*/

	/*

	function delete_value( $post_id, $key ) {



	}

	*/


	/*
	*  load_field()
	*
	*  This filter is applied to the $field after it is loaded from the database
	*
	*  @type	filter
	*  @date	23/01/2013
	*  @since	3.6.0
	*
	*  @param	$field (array) the field array holding all the field options
	*  @return	$field
	*/

	/*

	function load_field( $field ) {

		return $field;

	}

	*/


	/*
	*  update_field()
	*
	*  This filter is applied to the $field before it is saved to the database
	*
	*  @type	filter
	*  @date	23/01/2013
	*  @since	3.6.0
	*
	*  @param	$field (array) the field array holding all the field options
	*  @return	$field
	*/

	/*

	function update_field( $field ) {

		return $field;

	}

	*/


	/*
	*  delete_field()
	*
	*  This action is fired after a field is deleted from the database
	*
	*  @type	action
	*  @date	11/02/2014
	*  @since	5.0.0
	*
	*  @param	$field (array) the field array holding all the field options
	*  @return	n/a
	*/

	/*

	function delete_field( $field ) {



	}

	*/


}


// initialize
new lgnd_acf_field_responsive_image( $this->settings );


// class_exists check
endif;

?>
