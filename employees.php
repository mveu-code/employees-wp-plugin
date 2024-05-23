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
}