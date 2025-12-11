<?php

/**
 * @file
 * Theme hooks and preprocess functions for nodes.
 */

/**
 * Implements hook_theme_suggestions_HOOK_alter().
 */
function kinetic_theme_suggestions_node_alter(array &$suggestions, array $variables) {
  // Determine if we need to include $node . '__' . $bundle in the suggestions.
}
