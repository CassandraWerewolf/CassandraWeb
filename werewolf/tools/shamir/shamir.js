// Shamir's secret sharing, done entirely in the browser.
// Nothing typed into this page is sent to the server.
//
// Uses the audited shamir-secret-sharing library (v0.0.4, Apache-2.0), kept
// unmodified in ./vendor/. Share format: "[identifier-]number-hex", where the
// hex part holds the share itself; only the part after the last "-" is used
// when combining. Shares from the old server-side tool (ssss) are not compatible.

import { split, combine } from './vendor/shamir-secret-sharing-0.0.4/index.js';

const MAX_SHARES = 50;
const MAX_SECRET_CHARS = 255;
const MAX_IDENT_CHARS = 20;

const $ = (id) => document.getElementById(id);

function showStatus(message, isError = false) {
  const status = $('sss_status');
  status.textContent = message;
  status.style.color = isError ? '#b00020' : '';
}

function toHex(bytes) {
  return Array.from(bytes, (b) => b.toString(16).padStart(2, '0')).join('');
}

function fromHex(hex) {
  if (!/^([0-9a-fA-F]{2})+$/.test(hex)) {
    throw new Error(`Not a valid share: "${hex.slice(0, 20)}…"`);
  }
  return Uint8Array.from(hex.match(/../g), (pair) => parseInt(pair, 16));
}

function readInt(id, label) {
  const text = $(id).value.trim();
  if (!/^\d+$/.test(text)) {
    throw new Error(`${label} must be a whole number.`);
  }
  return Number(text);
}

async function getShares() {
  const shares = readInt('num_shares', 'Number of shares');
  const threshold = readInt('num_thresh', 'Shares threshold');
  const ident = $('ident').value.trim();
  const secretText = $('secret').value;

  if (shares < 2 || shares > MAX_SHARES) {
    throw new Error(`Number of shares must be between 2 and ${MAX_SHARES}.`);
  }
  if (threshold < 2 || threshold > shares) {
    throw new Error('Shares threshold must be at least 2 and no more than the number of shares.');
  }
  if (ident.length > MAX_IDENT_CHARS || /\s/.test(ident)) {
    throw new Error(`Identifier must be at most ${MAX_IDENT_CHARS} characters, with no spaces.`);
  }
  if (secretText.length === 0 || secretText.length > MAX_SECRET_CHARS) {
    throw new Error(`Secret must be 1 to ${MAX_SECRET_CHARS} characters.`);
  }

  const parts = await split(new TextEncoder().encode(secretText), shares, threshold);
  const prefix = ident ? `${ident}-` : '';
  $('shares_list').value = parts.map((part, i) => `${prefix}${i + 1}-${toHex(part)}`).join('\n');
  showStatus(`Created ${shares} shares; any ${threshold} of them recover the secret.`);
}

async function getSecret() {
  const lines = $('shares_list').value
    .split('\n')
    .map((line) => line.trim())
    .filter((line) => line.length > 0);
  if (lines.length < 2) {
    throw new Error('Paste at least 2 shares, one per line.');
  }

  // With too few shares the maths silently produces a wrong secret, so use the
  // threshold field (if filled in) as a check.
  const thresholdText = $('num_thresh').value.trim();
  if (thresholdText) {
    const threshold = readInt('num_thresh', 'Shares threshold');
    if (lines.length < threshold) {
      throw new Error(`Only ${lines.length} shares given; this secret needs ${threshold}.`);
    }
  }

  const parts = lines.map((line) => fromHex(line.slice(line.lastIndexOf('-') + 1)));
  const secret = await combine(parts);
  try {
    $('secret').value = new TextDecoder('utf-8', { fatal: true }).decode(secret);
  } catch {
    throw new Error('These shares do not combine into readable text: some may be wrong or from different secrets.');
  }
  showStatus('Secret recovered.');
}

function reset() {
  for (const field of document.querySelectorAll('textarea')) {
    field.value = '';
  }
  showStatus('');
}

const actions = { get_shares: getShares, get_secret: getSecret };

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
