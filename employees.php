<?php 
/* Plugin name: Работники компании */

add_action('init', 'main');

function main()
{
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
        'supports' => array('title', 'editor', 'author', 'thumbnail', 'trackbacks', 'custom-fields', 'comments', 'revisions')
    );

    register_post_type('staff', $args); 

    add_shortcode( 'staff_list', 'staff_list_shortcode' );

    add_action( 'save_post', 'true_save_meta_staff', 10, 2 );
}

add_action('add_meta_boxes', 'staff_add_custom_box');

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

            $staff_name = get_post_meta( get_the_ID(), 'employee_name', true );
            $staff_age = get_post_meta( get_the_ID(), 'employee_age', true );
            $staff_phone = get_post_meta( get_the_ID(), 'employee_phone', true );
            $image_url = get_post_meta( get_the_ID(), 'employee_photo', true ); // Получаем URL изображения

            $output .= '<div class="staff-item">';
            if (!empty($image_url)) { // Если изображение есть
                $output .= '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($staff_name) . '">'; 
            }
            $output .= '<h3>' . esc_html($staff_name) . '</h3>';
            if (!empty($staff_age)) {
                $output .= '<p>Возраст: ' . esc_html($staff_age) . ' лет</p>';
            }
            if (!empty($staff_phone)) {
                $output .= '<p>Телефон: ' . esc_html($staff_phone) . '</p>';
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

function staff_add_custom_box( $post ) {
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
    // Сначала получаем значения этих полей
    $staff_name = get_post_meta( $post->ID, 'employee_name', true );
    $staff_age = get_post_meta( $post->ID, 'employee_age', true );
    $staff_phone = get_post_meta( $post->ID, 'employee_phone', true );

    // Проверяем, была ли отправка файла
    if ( isset( $_FILES['image'] ) && ! empty( $_FILES['image'] ) ) {
        // Получаем информацию о загруженном файле
        $upload = wp_handle_upload( $_FILES['image'], array( 'test_form' => false ) );

        // Если файл был загружен успешно, сохраняем его URL в метаданных поста
        if ( ! function_exists( 'wp_handle_upload' ) ) {
            echo 'Функция wp_handle_upload не найдена.';
            return;
        }
        if ( $upload && ! isset( $upload['error'] ) || $upload['error'] == 0 ) {
            update_post_meta( $post->ID, 'employee_photo', $upload['url'] );
        } else {
            // Если файл не был загружен, удаляем метаданные
            delete_post_meta( $post->ID, 'employee_photo' );
        }
    } else {
        // Если файл не был отправлен, удаляем метаданные
        delete_post_meta( $post->ID, 'employee_photo' );
    }

    wp_nonce_field( 'admin-employees-' . $post->ID, '_truenonce' );

?>
    
    <label for="name">Изображение сотрудника</label>
    <input type="file" id="image" name="image" class="regular-text">
    <br />

    <label for="name">Имя сотрудника</label>
    <input type="text" value="<?php echo esc_attr($staff_name); ?>" id="name" name="name" class="regular-text">

    <br />

    <label for="age">Возраст сотрудника</label>
    <input type="number" value="<?php echo esc_attr($staff_age); ?>" id="age" name="age" class="regular-text">

    <br />

    <label for="phone">Номер телефона сотрудника</label>
    <input type="tel" value="<?php echo esc_attr($staff_phone); ?>" id="phone" name="phone" class="regular-text">
<?php
}

function true_save_meta_staff( $post_id, $post ) {
    // проверка одноразовых полей
    if ( ! isset( $_POST[ '_truenonce' ] ) || ! wp_verify_nonce( $_POST[ '_truenonce' ], 'admin-employees-' . $post->ID ) ) {
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
   
    if( isset( $_POST[ 'name' ] ) ) {
      update_post_meta( $post_id, 'employee_name', sanitize_text_field( $_POST[ 'name' ] ) );
    } else {
      delete_post_meta( $post_id, 'employee_name' );
    }
  
    if( isset( $_POST[ 'age' ] ) ) {
      update_post_meta( $post_id, 'employee_age', sanitize_text_field( $_POST[ 'age' ] ) );
    } else {
      delete_post_meta( $post_id, 'employee_age' );
    }
  
    if( isset( $_POST[ 'phone' ] ) ) {
      update_post_meta( $post_id, 'employee_phone', sanitize_text_field( $_POST[ 'phone' ] ) );
    } else {
      delete_post_meta( $post_id, 'employee_phone' );
    }


    
    if( isset( $_FILES[ 'image' ] ) && ! empty( $_FILES[ 'image' ] ) ) {
      $upload = wp_handle_upload( $_FILES[ 'image' ], array( 'test_form' => false ) );
      if ( isset( $upload[ 'error' ] ) ) {
          error_log( "Ошибка загрузки изображения: " . $upload[ 'error' ] );
      } else {
          if ( isset( $upload[ 'url' ] ) ) {
              update_post_meta( $post_id, 'employee_photo', $upload[ 'url' ] ); 
          }
      }
    } else {
      delete_post_meta( $post_id, 'employee_photo' );
    } 
    
    return $post_id;
  }

