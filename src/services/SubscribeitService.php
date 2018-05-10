<?php
/**
 * subscribeit plugin for Craft CMS 3.x
 *
 * Follow, Favourite, Bookmark, Like & Subscribe.
 *
 * @link      https://fruitstudios.co.uk
 * @copyright Copyright (c) 2018 Fruit Studios
 */

namespace fruitstudios\subscribeit\services;

use fruitstudios\subscribeit\Subscribeit;

use Craft;
use craft\base\Component;

/**
 * @author    Fruit Studios
 * @package   Subscribeit
 * @since     1.0.0
 */
class SubscribeitService extends Component
{
    // Public Methods
    // =========================================================================

    public function saveRecipe(Recipe $recipe, $runValidation = true)
    {
        // Fire a 'beforeSaveRecipe' event
        $this->trigger(self::EVENT_BEFORE_SAVE_RECIPE, new RecipeEvent([
            'recipe' => $recipe,
            'isNew' => $isNewRecipe,
        ]));

        if ($runValidation && !$recipe->validate()) {
            \Craft::info('Recipe not saved due to validation error.', __METHOD__);
            return false;
        }

        $isNewRecipe = !$recipe->id;

        // ... Save the recipe here ...

        // Fire an 'afterSaveRecipe' event
        $this->trigger(self::EVENT_AFTER_SAVE_RECIPE, new RecipeEvent([
            'recipe' => $recipe,
            'isNew' => $isNewRecipe,
        ]));

        return true;
    }


    /*
     * @return mixed
     */
    public function exampleService()
    {
        $result = 'something';

        return $result;
    }
}
