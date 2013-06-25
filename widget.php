<?php

/**
 * Adds Converter_Widget widget.
 */
class Converter_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
	 		'Converter_widget', // Base ID
			'Converter Widget', // Name
			array( 'description' => __( 'Converter Widget', 'text_domain' ), ) // Args
		);

	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {

		
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $before_widget;
		if ( ! empty( $title ) )
			echo $before_title . $title . $after_title;

		//Get ingredients from file

		$i = 0;
		if (($handle = fopen(plugin_dir_path( __FILE__ ). "/data/ingredients.csv", "r")) !== FALSE) {
		    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		    	if ($i != 0 && $data[0] != "") {
		    		$name = strtolower($data[0]);
		    		$name = str_replace(' ', '_', $name);
		    		$name = str_replace(',', '', $name);
		    		$ingredients[$name]['title'] = $data[0];
		    		$ingredients[$name]['density'] = $data[1];	    		
		    	}
		    		
		        $i++;
		    }
		    fclose($handle);
		}
		ksort($ingredients);

		//Get measurements from file

		$i = 0;
		if (($handle = fopen(plugin_dir_path( __FILE__ ). "/data/measurements.csv", "r")) !== FALSE) {
		    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		    	if ($i != 0 && $data[0] != "") {
		    		$name = strtolower($data[0]);
		    		$name = str_replace(' ', '_', $name);
		    		$name = str_replace(',', '', $name);
		    		$measurements[$name]['title'] = $data[0];
		    		$measurements[$name]['short'] = $data[1];
		    		$measurements[$name]['type'] = $data[2];
		    		$measurements[$name]['value'] = $data[3];
		    	}
		    		
		        $i++;
		    }
		    fclose($handle);
		}
		ksort($measurements);
		
		function show_measurements_options($measurements) {
			foreach ($measurements as $name => $data) {
				
				echo '<option value="' . $name . '" data-type="' . $data['type'] . '" data-value="' . $data['value'] . '">';
				echo $data['title'];
				echo '</option>';
			}
		}
		function show_ingredients_options($ingredients) {
			foreach ($ingredients as $name => $data) {
				echo '<option value="' . $name . '" data-density="' . $data['density'] . '">';
				echo $data['title'];
				echo '</option>';
			}
		}

		//Widget content
		?>
		<div class="converter">
			<p>
				Convert <input type="text" value="1" name="amount" id="amount" style="width: 30px;" /> <select id="measurement_from"><?php show_measurements_options($measurements); ?></select></p><p>of <select id="ingredient"><?php show_ingredients_options($ingredients); ?></select></p><p> in <select id="measurement_to"><?php show_measurements_options($measurements); ?></select></p>
			</p>
			<p id="result"></p>
		</div>
		
		<button class="convert_button" onclick="convert()">Convert</button>
		<script>
			function convert() {
				
				var amount = document.getElementById("amount").value,
					measurement_from = document.getElementById("measurement_from"),
					measurement_from_name = measurement_from.options[measurement_from.selectedIndex].text,
					measurement_from_type = measurement_from.options[measurement_from.selectedIndex].getAttribute('data-type'),
					measurement_from_value = measurement_from.options[measurement_from.selectedIndex].getAttribute('data-value'),
					measurement_to = document.getElementById("measurement_to"),
					measurement_to_name = measurement_to.options[measurement_to.selectedIndex].text,
					measurement_to_type = measurement_to.options[measurement_to.selectedIndex].getAttribute('data-type'),
					measurement_to_value = measurement_to.options[measurement_to.selectedIndex].getAttribute('data-value'),
					ingredient = document.getElementById("ingredient"),
					ingredient_name = ingredient.options[ingredient.selectedIndex].text,
					ingredient_density = ingredient.options[ingredient.selectedIndex].getAttribute('data-density'),
					total_volume,
					result,
					result_text,
					result_div = document.getElementById("result");

					if (measurement_from_type == 'weight')
						total_volume = amount * measurement_from_value / ingredient_density;
					else 
						total_volume = amount * measurement_from_value;

					if (measurement_to_type == 'weight')
						result = total_volume / measurement_to_value * ingredient_density;
					else
						result = total_volume / measurement_to_value;

					result = Math.round(result * 100) / 100;
					result_text = amount + ' ' + measurement_from_name + ' of ' + ingredient_name + ' equals to ' + result + ' ' + measurement_to_name;
					while( result_div.firstChild ) {
					    result_div.removeChild( result_div.firstChild );
					}
					result_div.appendChild( document.createTextNode(result_text) );
				

			}
		</script>	
		<?php
		
		echo $after_widget;

		
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'Converter', 'text_domain' );
		}
		
		?>
		<p>
		<label for="<?php echo $this->get_field_name( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		
		<?php 
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( !empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}

} // class Converter_Widget



// register Converter_Widget widget
add_action( 'widgets_init', function() { register_widget( 'Converter_Widget' ); } );
