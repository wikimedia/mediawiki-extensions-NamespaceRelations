<?php

class NamespaceRelationsHooks {

	/**
	 * @param SkinTemplate $skinTemplate
	 * @param array $navigation
	 *
	 * @return bool
	 */
	public static function onSkinTemplateNavigation( $skinTemplate, $navigation ) {
		$test = new NamespaceRelations();
		$test->injectTabs( &$skinTemplate, &$navigation['namespaces'] );

		return true;
	}
}
