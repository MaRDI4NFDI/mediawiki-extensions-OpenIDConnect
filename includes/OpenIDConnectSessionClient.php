<?php
/*
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 */

namespace MediaWiki\Extension\OpenIDConnect;

use Jumbojett\OpenIDConnectClient;
use MediaWiki\Auth\AuthManager;
use MediaWiki\Session\Session;

/**
 * $_SESSION is local to one app server, the MediaWiki session is not.
 */
class OpenIDConnectSessionClient extends OpenIDConnectClient {

	public function __construct( private readonly AuthManager $authManager ) {
		parent::__construct();
	}

	private function getSession(): Session {
		return $this->authManager->getRequest()->getSession();
	}

	protected function commitSession() {
		// Runs right before the redirect to the provider, which exits.
		$session = $this->getSession();
		$session->persist();
		$session->save();
	}

	/** @inheritDoc */
	protected function getSessionKey( string $key ) {
		return $this->getSession()->getSecret( $key, false );
	}

	/** @inheritDoc */
	protected function setSessionKey( string $key, $value ) {
		$this->getSession()->setSecret( $key, $value );
	}

	protected function unsetSessionKey( string $key ) {
		$this->getSession()->remove( $key );
	}
}
