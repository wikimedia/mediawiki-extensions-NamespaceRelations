<?php

class NamespaceRelationsHooks {

	/**
	 * Hook handler for hook 'SkinTemplateNavigation'
	 * @param \SkinTemplate $skinTemplate
	 * @param array &$navigation
	 */
	public static function onSkinTemplateNavigationUniversal( $skinTemplate, &$navigation ) {
		$nsRelations = new NamespaceRelations();
		$nsRelations->injectTabs( $skinTemplate, $navigation['namespaces'] );
	}
}
