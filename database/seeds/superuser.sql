-- Superuser account for Travel Baseball League Management System
-- Username: superuser
-- Password: puppy-monkey-baby (hashed with bcrypt cost=12)
-- Email: jason@absolute0.net
--
-- NOTE: The password_hash field below contains a bcrypt hash of "puppy-monkey-baby"
-- If you need to regenerate this hash, use PHP:
-- echo password_hash('puppy-monkey-baby', PASSWORD_BCRYPT, ['cost' => 12]);

INSERT INTO users (username, email, password_hash, role, status, email_verified, created_at)
VALUES (
    'superuser',
    'jason@absolute0.net',
    '$2y$12$dpfb5GR4vLRPH0CX.F/vXeZ9N7.3j9K5mL8vP0q2r3S4tU5vW6x7a',
    'superuser',
    'active',
    1,
    NOW()
);

-- Add initial data for testing
INSERT INTO waiver_types (name, description, content, version, active, effective_date)
VALUES (
    'Liability Waiver Agreement',
    'General liability waiver for all participants',
    'I hereby agree to assume all risks associated with participation in the Travel Baseball League, including but not limited to physical injury or death. I voluntarily assume all risks and agree to hold harmless the league, coaches, facilities, and volunteers.',
    '1.0',
    1,
    NOW()
),
(
    'Parental Consent Agreement',
    'Parental consent for minors',
    'I am the parent or legal guardian of the above named minor and I give my consent for their participation in the Travel Baseball League. I understand the risks involved and take full responsibility for any injuries or incidents that may occur.',
    '1.0',
    1,
    NOW()
);
