<?php

class NamespaceRelations {

	const MAIN_WEIGHT = 10;
	const TALK_WEIGHT = 20;
	const STARTING_WEIGHT = 30;
	const WEIGHT_INCREMENT = 10;

	/**
	 * Processed $wgNamespaceRelations configuration
	 * @var array
	 */
	private $_namespaces;

	/**
	 * References to $this->_namespaces per target
	 * @var array
	 */
	private $_namespacesToTarget;

	/**
	 * References to $this->_namespaces per allowed namespace
	 * @var array
	 */
	private $_namespacesToNamespace;

	public function __construct() {
		global $wgNamespaceRelations;

		$this->_namespaces = array();
		if ( !empty( $wgNamespaceRelations ) ) {
			$sortingWeight = self::STARTING_WEIGHT;
			foreach ( $wgNamespaceRelations as $key => $data ) {
				$this->_namespaces[$key] = array(
					'message' => 'nstab-extra-' . $key,
					'namespace' => $data['namespace'],
					'target' => $data['target'],
					'inMainPage' => isset( $data['inMainPage'] ) ? $data['inMainPage'] : false,
					'query' => isset( $data['query'] ) ? $data['query'] : null,
					'hideTalk' => isset( $data['hideTalk'] ) ? $data['hideTalk'] : false
				);
				if ( !isset( $data['weight'] ) ) {
					$this->_namespaces[$key]['weight'] = $sortingWeight;
					$sortingWeight += self::WEIGHT_INCREMENT;
				} else {
					$this->_namespaces[$key]['weight'] = $data['weight'];
				}

				$this->addToNamespace( $data['namespace'], $key );
				$this->addToTarget( $data['target'], $key );
			}
		}
	}

	/**
	 * @param SkinTemplate $skinTemplate
	 * @param $navigation
	 */
	public function injectTabs( $skinTemplate, $navigation ) {
		$title = $skinTemplate->getRelevantTitle();
		$subjectNS = $title->getSubjectPage()->getNamespace();
		$userCanRead = $title->quickUserCan( 'read', $skinTemplate->getUser() );

		if ( array_key_exists( $subjectNS, $this->_namespacesToNamespace ) ) {
			foreach ( $this->_namespacesToNamespace[$subjectNS] as $key ) {
				if ( $title->isMainPage() && !$this->getNamespace( $key, 'inMainPage' ) ) {
					continue;
				}
				$tabTitle = Title::makeTitle( $this->_namespaces[$key]['target'], $title->getText() );
				$tabQuery = $this->getNamespace( $key, 'query', '' );
				if ( $tabTitle->isKnown() ) {
					$tabQuery = '';
				}
				$navigation[$key] = $skinTemplate->tabAction(
					$tabTitle, $this->getNamespace( $key, 'message' ), false, $tabQuery, $userCanRead
				);
				$navigation[$key]['weight'] = $this->getNamespace( $key, 'weight' );
			}
			$subjectId = $title->getNamespaceKey( '' );
			if ( $subjectId === 'main' ) {
				$talkId = 'talk';
			} else {
				$talkId = $subjectId . '_talk';
			}
			$navigation[$subjectId]['weight'] = self::MAIN_WEIGHT;
			$navigation[$talkId]['weight'] = self::TALK_WEIGHT;
			if ( $this->getNamespace( $key, 'hideTalk' ) ) {
				unset( $navigation[$talkId] );
			}
			$this->sortNavigation( &$navigation );
		} elseif ( array_key_exists( $subjectNS, $this->_namespacesToTarget ) ) {
			$key = $this->_namespacesToTarget[$subjectNS];
			$realSubjectNS = $this->getNamespace( $key, 'namespace' );
			$subjectTitle = Title::makeTitle( $realSubjectNS, $title->getText() );
			$talkTitle = Title::makeTitle( MWNamespace::getTalk( $realSubjectNS ), $title->getText() );

			$subjectId = $subjectTitle->getNamespaceKey( '' );
			if ( $subjectId === 'main' ) {
				$talkId = 'talk';
			} else {
				$talkId = $subjectId . '_talk';
			}
			$subjectMsg = array( 'nstab-' . $subjectId );
			if ( $subjectTitle->isMainPage() ) {
				array_unshift( $subjectMsg, 'mainpage-nstab' );
			}

			$navigation = array();
			$navigation[$subjectId] = $skinTemplate->tabAction(
				$subjectTitle, $subjectMsg, false, '', $userCanRead
			);
			$navigation[$subjectId]['weight'] = self::MAIN_WEIGHT;
			$navigation[$subjectId]['context'] = 'subject';
			$navigation[$talkId] = $skinTemplate->tabAction(
				$talkTitle, array( 'nstab-' . $talkId, 'talk' ), false, '', $userCanRead
			);
			$navigation[$talkId]['weight'] = self::TALK_WEIGHT;

			foreach ( $this->_namespacesToNamespace[$realSubjectNS] as $tabKey ) {
				$tabTitle = Title::makeTitle( $this->getNamespace( $tabKey, 'target' ), $title->getText() );
				$isActive = $skinTemplate->getTitle()->equals( $tabTitle );
				$tabQuery = $this->getNamespace( $key, 'query', '' );
				if ( $tabTitle->isKnown() ) {
					$tabQuery = '';
				}
				$navigation[$tabKey] = $skinTemplate->tabAction(
					$tabTitle, $this->getNamespace( $tabKey, 'message' ), $isActive, $tabQuery, $userCanRead
				);
				$navigation[$tabKey]['weight'] = $this->getNamespace( $tabKey, 'weight' );

				if ( isset( $navigation[$talkId] ) && $this->getNamespace( $tabKey, 'hideTalk' ) ) {
					unset( $navigation[$talkId] );
				}
			}
			$this->sortNavigation( &$navigation );
		}
	}

	private function sortNavigation( $navigation ) {
		uasort( &$navigation, function ( $first, $second ) {
			return $first['weight'] - $second['weight'];
		} );
	}

	/**
	 * Returns full NS tab definition or one of its fields
	 *
	 * @param string $key NS tab key
	 * @param string $param NS tab parameter
	 * @param mixed $default Value to return if parameter doesn't exist
	 *
	 * @return array|mixed
	 */
	private function getNamespace( $key, $param = null, $default = null ) {
		if ( is_null( $param ) && isset( $this->_namespaces[$key] ) ) {
			return $this->_namespaces[$key];
		} elseif ( isset( $this->_namespaces[$key][$param] ) && !is_null( $this->_namespaces[$key][$param] ) ) {
			return $this->_namespaces[$key][$param];
		} else {
			return $default;
		}
	}

	/**
	 * @param integer $ns Namespace ID
	 * @param string $key NS tab key
	 *
	 * @throws MWException Thrown if namespace doesn't exist
	 */
	private function addToNamespace( $ns, $key ) {
		if ( MWNamespace::exists( $ns ) ) {
			$this->_namespacesToNamespace[$ns][] = $key;
		} else {
			throw new MWException( "Namespace doesn't exist." );
		}
	}

	/**
	 * @param integer $ns Namespace ID
	 * @param string $key NS tab key
	 *
	 * @throws MWException Thrown if namespace doesn't exist
	 */
	private function addToTarget( $ns, $key ) {
		if ( MWNamespace::exists( $ns ) ) {
			$this->_namespacesToTarget[$ns] = $key;
		} else {
			throw new MWException( "Namespace doesn't exist." );
		}
	}
}
