<?php

class NamespaceRelationsHooks {

	/**
	 * @param SkinTemplate $skinTemplate
	 * @param array $navigation
	 *
	 * @return bool
	 */
	public static function onSkinTemplateNavigation( $skinTemplate, $navigation ) {
		//print_r($navigation);
		$test = new NamespaceRelations();
		$test->injectTabs( &$skinTemplate, &$navigation['namespaces'] );

		return true;
	}
}
