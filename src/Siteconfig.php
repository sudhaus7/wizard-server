<?php

namespace Sudhaus7\WizardServer;

use React\Promise\Deferred;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

class Siteconfig {
	protected  $pageid;

	public function __construct(int $pageid)
	{
		$this->pageid = $pageid;
	}

	public function fetch()
	{
		$deferred = new Deferred();
		$promise = $deferred->promise();


		$basedir = dirname(\getenv('WIZARD_SERVER_DOCROOT'));
		$result = [];
		if (\is_dir( $basedir.'/config/sites')) {
			$files  = Finder::create()->files()->name( '*.yaml' )->in( $basedir . '/config/sites/' );

			if ( $files->hasResults() ) {
				foreach ( $files as $file ) {
					$yaml = Yaml::parseFile( $file->getRealPath() );
					if ( isset( $yaml['rootPageId'] ) && (int) $yaml['rootPageId'] === $this->pageid ) {
						$result = $yaml;
					}
				}
			}
		}
		$deferred->resolve( $result );
		return $promise;
	}
}
