<?php 
/* Plugin name: Работники компании */

add_action( 'init', 'main' );

function main() {
    $taxLabels = [
        'name'              => 'Отделы',
        'singular_name'     => 'Отдел',
        'search_items'      => 'Поиск отделов',
        'all_items'         => 'Все отделы',
        'view_item '        => 'Просмотреть отдел',
        'parent_item'       => 'Родительский отдел',
        'parent_item_colon' => 'Родительский отдел:',
        'edit_item'         => 'Редактировать отдел',
        'update_item'       => 'Обновить отдел',
        'add_new_item'      => 'Добавить отдел',
        'new_item_name'     => 'Новый отдел',
        'menu_name'         => 'Отдел',
        'back_to_items'     => '← Вернуться к отделам',
    ];
    
    $taxArgs = [
        'public' => true,
        'labels' => $taxLabels,
        'hierarchical' => true,
    ];
    
  register_taxonomy('department', ['staff'], $taxArgs);
    
    
    $labels = array(
    'name' => 'Сотрудники',
    'singular_name' => 'Сотрудник',
    'add_new' => 'Добавить сотрудника',
    'add_new_item' => 'Добавить сотрудника',
    'edit_item' => 'Редактировать сотрудника',
    'new_item' => 'Новый сотрудник',
    'all_items' => 'Все Сотрудники',
    'search_items' => 'Искать Сотрудников',
    'not_found' =>  'Сотрудников по заданным критериям не найдено.',
    'not_found_in_trash' => 'В корзине нет сотрудников.',
    'menu_name' => 'Сотрудники'
  );
 
  $args = array(
    'labels' => $labels,
    'public' => true,
    'has_archive' => true,
    'menu_icon' => 'dashicons-email-alt2',
    'menu_position' => 3,
    'supports' => array('title','editor','author','thumbnail','trackbacks','custom-fields','comments','revisions')
  );
 
  register_post_type('staff', $args);

  add_action( 'add_meta_boxes', 'staff_add_custom_box' );

function staff_add_custom_box() {
  $screens = [ 'staff' ];
  foreach ( $screens as $screen ) {
    add_meta_box(
      'staff_metabox',           // Unique ID
      'Карточка сотрудника',     // Box title
      'staff_custom_box_html',   // Content callback, must be of type callable
      $screen,                   // типы постов, для которых его подключим
            'normal', // расположение (normal, side, advanced)
            'high' // приоритет (default, low, high, core)
    );
  }
}

function staff_custom_box_html( $post ) {
    // сначала получаем значения этих полей
    $staff_phone = get_post_meta( $post->ID, 'phone', true );
    $staff_age = get_post_meta( $post->ID, 'age', true );
    wp_nonce_field( 'seopostsettingsupdate-' . $post->ID, '_truenonce' );
  ?>
    <label for="phone_box">Телефон сотрудника</label>
    <input type="text" value="<?=esc_attr($staff_phone);?>" id="phone" name="phone" class="regular-text">
    
    <label for="age_box">Возраст сотрудника</label>
    <input type="number" value="<?=esc_attr($staff_age);?>" id="age" name="age" class="regular-text">
  <?php
}

function true_save_meta_staff( $post_id, $post ) {
 
  // проверка одноразовых полей
  if ( ! isset( $_POST[ '_truenonce' ] ) || ! wp_verify_nonce( $_POST[ '_truenonce' ], 'seopostsettingsupdate-' . $post->ID ) ) {
    return $post_id;
  }
 
  // проверяем, может ли текущий юзер редактировать пост
  $post_type = get_post_type_object( $post->post_type );
 
  if ( ! current_user_can( $post_type->cap->edit_post, $post_id ) ) {
    return $post_id;
  }
 
  // ничего не делаем для автосохранений
  if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
    return $post_id;
  }
 
  // проверяем тип записи
  if( !in_array($post->post_type, array('staff')) ) {
    return $post_id;
  }
 
  if( isset( $_POST[ 'phone' ] ) ) {
    update_post_meta( $post_id, 'phone', sanitize_text_field( $_POST[ 'phone' ] ) );
  } else {
    delete_post_meta( $post_id, 'phone' );
  }
  if( isset( $_POST[ 'age' ] ) ) {
    update_post_meta( $post_id, 'age', sanitize_text_field( $_POST[ 'age' ] ) );
  } else {
    delete_post_meta( $post_id, 'age' );
  }
 
  return $post_id;
}

add_shortcode( 'staff_list', 'staff_list_shortcode' );
}


function staff_list_shortcode() {
    $args = array(
        'post_type' => 'staff',
        'posts_per_page' => -1, // Выводим все записи
    );

    $query = new WP_Query( $args );

    if ( $query->have_posts() ) {
        $output = '<div class="staff-list">';
        while ( $query->have_posts() ) {
            $query->the_post();

            $phone = get_post_meta( get_the_ID(), 'phone', true );
            $age = get_post_meta( get_the_ID(), 'age', true );

            $output .= '<div class="staff-item">';
            $output .= '<h3>' . get_the_title() . '</h3>';
            $output .= '<p>' . get_the_excerpt() . '</p>';
            if (!empty($phone)) {
                $output .= '<p>Телефон: ' . esc_html($phone) . '</p>';
            }
            if (!empty($age)) {
                $output .= '<p>Возраст: ' . esc_html($age) . ' лет</p>';
            }
            $output .= '</div>';
        }
        $output .= '</div>';
        wp_reset_postdata();
        return $output;
    } else {
        return 'Нет сотрудников';
    }
}
