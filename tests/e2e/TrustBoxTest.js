describe('Trust Box', () => {
  it('shows tooltip on hover', () => {
    const citySlug = 'sofia';
    const serviceSlug = 'mobile-dog-grooming';
    cy.visit(`/groomers/${citySlug}/${serviceSlug}`);
    cy.get('.trust-box__label').trigger('mouseenter');
    cy.get('#verified-tooltip').should('be.visible').and('contain', 'Background checked');
  });
});
