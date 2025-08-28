describe('Hero Section', () => {
  it('renders hero with dynamic city name and scrolls to listings', () => {
    const citySlug = 'sofia';
    const serviceSlug = 'mobile-dog-grooming';
    cy.visit(`/groomers/${citySlug}/${serviceSlug}`);
    cy.contains('.hero__title', 'Mobile Dog Grooming in').should('contain', 'We Come to You');
    cy.contains('.hero__title', 'Sofia');
    cy.get('.hero__cta').click();
    cy.hash().should('eq', '#groomer-listings');
  });
});
