<?php

namespace MediaWiki\Extension\OpenIDConnect\Tests;

use MediaWiki\Auth\AuthManager;
use MediaWiki\Extension\OpenIDConnect\OpenIDConnectSessionClient;
use MediaWiki\Request\FauxRequest;
use MediaWikiIntegrationTestCase;
use Wikimedia\TestingAccessWrapper;

/**
 * @covers \MediaWiki\Extension\OpenIDConnect\OpenIDConnectSessionClient
 */
class OpenIDConnectSessionClientTest extends MediaWikiIntegrationTestCase {

	private function newClient( FauxRequest $request ) {
		$authManager = $this->createMock( AuthManager::class );
		$authManager->method( 'getRequest' )->willReturn( $request );
		return TestingAccessWrapper::newFromObject( new OpenIDConnectSessionClient( $authManager ) );
	}

	public function testKeepsLoginStateInMediaWikiSession() {
		$request = new FauxRequest();
		$client = $this->newClient( $request );
		$client->setState( 'state' );
		$client->setNonce( 'nonce' );
		$client->setCodeVerifier( 'verifier' );

		$session = $request->getSession();
		$this->assertSame( 'state', $session->getSecret( 'openid_connect_state' ) );
		$this->assertSame( 'nonce', $session->getSecret( 'openid_connect_nonce' ) );
		$this->assertSame( 'verifier', $session->getSecret( 'openid_connect_code_verifier' ) );
		$this->assertSame( PHP_SESSION_NONE, session_status() );

		$callbackClient = $this->newClient( $request );
		$this->assertSame( 'state', $callbackClient->getState() );
		$this->assertSame( 'nonce', $callbackClient->getNonce() );
		$this->assertSame( 'verifier', $callbackClient->getCodeVerifier() );

		$callbackClient->unsetState();
		$callbackClient->unsetNonce();
		$callbackClient->unsetCodeVerifier();
		$this->assertFalse( $client->getState() );
		$this->assertFalse( $client->getNonce() );
		$this->assertFalse( $client->getCodeVerifier() );
	}

	public function testCommitSessionPersistsSession() {
		$request = new FauxRequest();
		$client = $this->newClient( $request );
		$client->setState( 'state' );
		$this->assertFalse( $request->getSession()->isPersistent() );

		$client->commitSession();
		$this->assertTrue( $request->getSession()->isPersistent() );
	}
}
