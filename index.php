<?php
/*
 * Plugin Name: Inherited Products Category List
  * Description: The <code>[IPCL]</code> shortcode displays a list of all product categories, including inherited ones. It is useful for e-commerce websites.
 * Author: Manuel De los Reyes
 * Author URI: http://sirtexs.com
 * Version: 1.0
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

class WC_IPCL
{

    /*
     * Bootstraps the class and hooks required actions & filters.
     *
     */
    public static function init()
    {
        add_shortcode('IPCL', __CLASS__ . '::get_list_categories');
    }


    public function get_product_categories_hierarchy($term_id = 0)
    {
        return get_categories(array(
            'taxonomy' => 'product_cat', // nombre de la taxonomía de WooCommerce para las categorías de productos
            'orderby' => 'name', // ordenar las subcategorías por el nombre
            'parent' => $term_id, // obtener solo las subcategorías de esta categoría
            'hide_empty' => 0
        ));
    }

    public function get_elements($array = [], $i = 0)
    {
        if (!is_array($array)) return;
        // Construir un arreglo de cadenas de cada nivel de la lista
        $result = array();
        $count = count($array); // Obtener el número total de elementos en el arreglo

        foreach ($array as $key => $value) {
            if ($key > 0) $string .= ' > ';
            if ($key === $count - 1) {
                $string .= ' <b>' . $value . '</b>';
            } else {
                $string .= $value;
            }
            $result[] = $string;
        }
        // Ordenar el arreglo result al revés
        $result = array_reverse($result);

        // Imprimir las cadenas resultantes
        echo '<li>';
        echo implode(', ', $result);
        echo '</li>';
    }



    public function get_list_categories()
    {

        // Obtener todas las categorías
        $categories = self::get_product_categories_hierarchy();

        // Recorrer cada categoría y sus subcategorías para construir la lista en HTML
        echo '<ul>';
        foreach ($categories as $category) {
            self::get_elements([$category->name]);
            self::get_list_categories_recursive($category->term_id, [$category->name]);
        }
        echo '</ul>';
    }

    public function get_list_categories_recursive($parent_id = 0, $ancestors = [], $i = 0)
    {
        $subcategories = self::get_product_categories_hierarchy($parent_id);
        if (!empty($subcategories)) {
            $i++;
            foreach ($subcategories as $subcategory) {
                // Verificar si $ancestors es un arreglo y convertirlo en un arreglo vacío si no lo es
                $new_ancestors = is_array($ancestors) ? $ancestors : [];
                // Agregar el nombre de la subcategoría actual a la lista de ancestros
                $new_ancestors[] = $subcategory->name;
                self::get_elements($new_ancestors, $i);
                self::get_list_categories_recursive($subcategory->term_id, $new_ancestors, $i);
            }
        }
    }
}

WC_IPCL::init();
