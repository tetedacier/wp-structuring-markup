<?php
/**
 * Breadcrumb ShortCode Settings
 *
 * @author  Kazuya Takami
 * @version 2.0.0
 * @since   2.0.0
 */
class Structuring_Markup_ShortCode_Breadcrumb {

	/**
	 * ShortCode Display.
	 *
	 * @since  2.0.0
	 * @access public
	 * @param  array  $options
	 * @return string $html
	 */
	public function short_code_display( array $options ) {
		$item_array = $this->breadcrumb_array_setting( $options );
		$html = '';

		if ( $item_array ) {
			$html .= '<!-- Markup (JSON-LD) structured in schema.org Breadcrumb START -->' . PHP_EOL;
			$html .= '<ol>' . PHP_EOL;
			foreach ($item_array as $item) {
				$html .= '<li>';
				$html .= '<a href="' . esc_url($item['@id']) . '">';
				$html .= esc_html($item['name']);
				$html .= '</a>';
				$html .= '</li>' . PHP_EOL;
			}
			$html .= '</ol>' . PHP_EOL;
			$html .= '<!-- Markup (JSON-LD) structured in schema.org Breadcrumb END -->' . PHP_EOL;
		}

		return (string) $html;
	}

	/**
	 * Breadcrumb array setting.
	 *
	 * @since  2.0.0
	 * @access public
	 * @param  array $options
	 * @return array $item_array
	 */
	public function breadcrumb_array_setting( array $options ) {
		global $post;

		/** item build */
		$item_array = array();
		$current_url = esc_url( home_url() . $_SERVER['REQUEST_URI'] );

		if ( isset( $options['home_on'] ) &&  $options['home_on'] === 'on' ) {
			if ( isset( $options['home_name'] ) ) {
				$item_array[] = $this->set_schema_breadcrumb_item( home_url(), $options['home_name'] );
			} else {
				$item_array[] = $this->set_schema_breadcrumb_item( home_url(), get_bloginfo('name') );
			}
		}

		if ( is_search() ) {
			$item_array[] = $this->set_schema_breadcrumb_item( $current_url, get_search_query() );
		} elseif ( is_tag() ) {
			$item_array[] = $this->set_schema_breadcrumb_item( $current_url, single_tag_title( '', false ) );
		} elseif ( is_date() ) {
			$item_array[] = $this->set_schema_breadcrumb_item( get_year_link( get_query_var( 'year' ) ), get_query_var( 'year' ) );
			if ( get_query_var( 'day' ) !== 0 ) {
				$item_array[] = $this->set_schema_breadcrumb_item( get_month_link( get_query_var( 'year' ), get_query_var( 'monthnum' ) ), get_query_var( 'monthnum' ) );
				$item_array[] = $this->set_schema_breadcrumb_item( get_day_link( get_query_var( 'year' ), get_query_var( 'monthnum' ), get_query_var( 'day' ) ), get_query_var( 'day' ) );
			} elseif ( get_query_var( 'monthnum' ) !== 0 ) {
				$item_array[] = $this->set_schema_breadcrumb_item( get_month_link( get_query_var( 'year' ), get_query_var( 'monthnum' ) ), get_query_var( 'monthnum' ) );
			}
		} elseif ( is_category() ) {
			$categories = get_queried_object();
			if( $categories->parent !== 0 ) {
				$ancestors = array_reverse( get_ancestors( $categories->cat_ID, 'category' ) );
				foreach( $ancestors as $ancestor ) {
					$item_array[] = $this->set_schema_breadcrumb_item( get_category_link( $ancestor ), get_cat_name( $ancestor ) );
				}
			}
			$item_array[] = $this->set_schema_breadcrumb_item( get_category_link( $categories->term_id ), $categories->name );
		} elseif ( is_author() ) {
			$item_array[] = $this->set_schema_breadcrumb_item( $current_url, get_the_author_meta( 'display_name', get_query_var( 'author' ) ) );
		} elseif ( is_page() ) {
			if( $post->post_parent !== 0 ) {
				$ancestors = array_reverse( get_post_ancestors( $post->ID ) );
				foreach( $ancestors as $ancestor ){
					$item_array[] = $this->set_schema_breadcrumb_item( get_permalink( $ancestor ), get_the_title( $ancestor ) );
				}
			}
			$item_array[] = $this->set_schema_breadcrumb_item( $current_url, $post->post_title );
		} elseif ( is_attachment() ) {
			if ( $post->post_parent !== 0 ) {
				$item_array[] = $this->set_schema_breadcrumb_item( get_permalink( $post->post_parent ), get_the_title( $post->post_parent ) );
			}
			$item_array[] = $this->set_schema_breadcrumb_item( $current_url, $post->post_title );
		} elseif ( is_single() ) {
			$categories = get_the_category($post->ID);
			$cat = $categories[0];

			if( $cat->parent !== 0){
				$ancestors = array_reverse( get_ancestors( $cat->cat_ID, 'category' ) );
				foreach( $ancestors as $ancestor ){
					$item_array[] = $this->set_schema_breadcrumb_item( get_category_link( $ancestor ), get_cat_name( $ancestor ) );
				}
			}
			$item_array[] = $this->set_schema_breadcrumb_item( get_category_link( $cat->term_id ), $cat->name );
			$item_array[] = $this->set_schema_breadcrumb_item( $current_url, $post->post_title );
		}

		return (array) $item_array;
	}

	/**
	 * Breadcrumb item settings
	 *
	 * @since   2.0.0
	 * @version 2.0.0
	 * @param   string $id
	 * @param   string $name
	 * @return  array  $args
	 */
	private function set_schema_breadcrumb_item( $id, $name ) {
		$args = array(
			"@id"  => esc_html( $id ),
			"name" => esc_html( $name )
		);
		return (array) $args;
	}
}