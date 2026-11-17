// RSA public-key encryption, done entirely in the browser with the Web Crypto API.
// Nothing typed into this page is sent to the server.
//
// Algorithm: RSA-OAEP, 2048-bit keys, SHA-256.
// Keys use standard PEM: "PRIVATE KEY" (PKCS#8) and "PUBLIC KEY" (SPKI).
// Note: the old server-side tool used RSA-OAEP with SHA-1, so messages encrypted
// with it cannot be decrypted here.

const ALGORITHM = { name: 'RSA-OAEP', hash: 'SHA-256' };
const KEY_BITS = 2048;
// Largest message RSA-OAEP can encrypt: key bytes - 2 * hash bytes - 2.
const MAX_MESSAGE_BYTES = KEY_BITS / 8 - 2 * 32 - 2; // 190
const MESSAGE_LABEL = 'RSA-OAEP-SHA256 ENCRYPTED MESSAGE';

const $ = (id) => document.getElementById(id);

function showStatus(message, isError = false) {
  const status = $('rsa_status');
  status.textContent = message;
  status.style.color = isError ? '#b00020' : '';
}

// --- PEM / base64 helpers ---------------------------------------------------

function toBase64(buffer) {
  let binary = '';
  for (const byte of new Uint8Array(buffer)) {
    binary += String.fromCharCode(byte);
  }
  return btoa(binary);
}

function fromBase64(text) {
  const binary = atob(text.replace(/\s+/g, ''));
  const bytes = new Uint8Array(binary.length);
  for (let i = 0; i < binary.length; i++) {
    bytes[i] = binary.charCodeAt(i);
  }
  return bytes.buffer;
}

function toPem(label, buffer) {
  const lines = toBase64(buffer).match(/.{1,64}/g).join('\n');
  return `-----BEGIN ${label}-----\n${lines}\n-----END ${label}-----`;
}

// Returns the base64 body of a PEM block (drops the BEGIN/END lines).
function pemBody(text) {
  return text
    .split('\n')
    .filter((line) => !line.startsWith('-----'))
    .join('');
}

function importPrivateKey(pem) {
  return crypto.subtle.importKey('pkcs8', fromBase64(pemBody(pem)), ALGORITHM, true, ['decrypt']);
}

function importPublicKey(pem) {
  return crypto.subtle.importKey('spki', fromBase64(pemBody(pem)), ALGORITHM, true, ['encrypt']);
}

// --- Actions ------------------------------------------------------------------

// Creates a new key pair, or, if a private key is already filled in,
// (re)creates its matching public key (same behavior as the old tool).
async function getKeys() {
  const privateText = $('priv_key').value.trim();
  if (privateText) {
    const privateKey = await importPrivateKey(privateText);
    const { n, e } = await crypto.subtle.exportKey('jwk', privateKey);
    const publicKey = await crypto.subtle.importKey(
      'jwk', { kty: 'RSA', n, e, alg: 'RSA-OAEP-256', ext: true }, ALGORITHM, true, ['encrypt']);
    $('pub_key').value = toPem('PUBLIC KEY', await crypto.subtle.exportKey('spki', publicKey));
    showStatus('Public key created from the private key.');
    return;
  }
  const pair = await crypto.subtle.generateKey(
    { ...ALGORITHM, modulusLength: KEY_BITS, publicExponent: new Uint8Array([1, 0, 1]) },
    true, ['encrypt', 'decrypt']);
  $('priv_key').value = toPem('PRIVATE KEY', await crypto.subtle.exportKey('pkcs8', pair.privateKey));
  $('pub_key').value = toPem('PUBLIC KEY', await crypto.subtle.exportKey('spki', pair.publicKey));
  showStatus('New key pair created. Keep the private key secret.');
}

async function encrypt() {
  const publicText = $('pub_key').value.trim();
  if (!publicText) {
    throw new Error('Paste or create a public key first.');
  }
  const message = new TextEncoder().encode($('plain_text').value);
  if (message.byteLength > MAX_MESSAGE_BYTES) {
    throw new Error(`Plaintext is too long: ${message.byteLength} bytes (maximum ${MAX_MESSAGE_BYTES}).`);
  }
  const publicKey = await importPublicKey(publicText);
  const encrypted = await crypto.subtle.encrypt(ALGORITHM, publicKey, message);
  $('cipher_text').value = toPem(MESSAGE_LABEL, encrypted);
  showStatus('Encrypted.');
}

async function decrypt() {
  const privateText = $('priv_key').value.trim();
  const cipherText = $('cipher_text').value.trim();
  if (!privateText || !cipherText) {
    throw new Error('Paste the private key and the ciphertext first.');
  }
  const privateKey = await importPrivateKey(privateText);
  try {
    const decrypted = await crypto.subtle.decrypt(ALGORITHM, privateKey, fromBase64(pemBody(cipherText)));
    $('plain_text').value = new TextDecoder().decode(decrypted);
    showStatus('Decrypted.');
  } catch (err) {
    if (cipherText.includes('RSAES-OAEP ENCRYPTED MESSAGE')) {
      throw new Error('This message was made with the old version of this tool (SHA-1), which is no longer supported.');
    }
    throw new Error('Could not decrypt: wrong private key, or the ciphertext was changed.');
  }
}

function reset() {
  for (const field of document.querySelectorAll('textarea')) {
    field.value = '';
  }
  showStatus('');
}

// --- Wiring -------------------------------------------------------------------

const actions = { get_pkeys: getKeys, rsa_pub_enc: encrypt, rsa_priv_dec: decrypt };

for (const button of document.querySelectorAll('button')) {
  button.addEventListener('click', async (event) => {
    event.preventDefault();
    if (button.classList.contains('reset')) {
      reset();
      return;
    }
    try {
      await actions[button.id]();
    } catch (err) {
      showStatus(err.message || String(err), true);
    }
  });
}

if (!window.crypto || !crypto.subtle) {
  showStatus('This browser cannot run this tool (Web Crypto is unavailable). Use a current browser over https.', true);
}
