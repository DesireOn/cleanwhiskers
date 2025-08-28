describe('Email Capture Block', () => {
  it('validates and submits lead capture form', () => {
    const citySlug = 'sofia';
    const serviceSlug = 'mobile-dog-grooming';
    cy.visit(`/groomers/${citySlug}/${serviceSlug}`);
    cy.contains('.email-capture__prompt', 'Not ready to book yet?');

    cy.intercept('POST', '/lead-capture', {
      statusCode: 201,
      body: { success: true },
    }).as('lead');

    cy.get('.email-capture__form [name="name"]').type('Tester');
    cy.get('.email-capture__form [name="email"]').type('tester@example.com');
    cy.get('.email-capture__form').submit();
    cy.wait('@lead');
    cy.contains('.email-capture__message', 'Thanks').should('be.visible');
  });
});

