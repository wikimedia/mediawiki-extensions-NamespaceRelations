<?php

class NamespaceRelationsHooks {

	/**
	 * Hook handler for hook 'SkinTemplateNavigation'
	 * @param \SkinTemplate $skinTemplate
	 * @param array &$navigation
	 */
	public static function onSkinTemplateNavigationUniversal( $skinTemplate, &$navigation ) {
		$nsKey = $skinTemplate->supportsMenu( 'namespaces' ) ? 'namespaces' : 'associated-pages';
		if ( !isset( $navigation[$nsKey] ) ) {
			$navigation[$nsKey] = [];
		}
		$nsRelations = new NamespaceRelations();
		$nsRelations->injectTabs( $skinTemplate, $navigation[$nsKey] );
	}
}
