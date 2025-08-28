describe('Groomer Listing Cards', () => {
  const citySlug = 'sofia';
  const serviceSlug = 'mobile-dog-grooming';

  it('renders core details for each card', () => {
    cy.visit(`/groomers/${citySlug}/${serviceSlug}`);
    cy.get('.cards .card').should('have.length.at.least', 1);
    cy.get('.cards .card').first().within(() => {
      cy.get('.card__title').should('not.be.empty');
      cy.get('.card__description').should('not.be.empty');
      cy.get('.card__rating').should('contain', '(');
      cy.get('.card__badge').should('contain', 'Verified');
      cy.contains('Book Now').should('have.attr', 'href');
    });
  });

  it('shows disclaimer only for placeholder testimonials', () => {
    cy.visit(`/groomers/${citySlug}/${serviceSlug}`);
    cy.get('.card__testimonial[data-placeholder="true"]').each(($el) => {
      cy.wrap($el).siblings('.card__disclaimer').should('exist');
    });
  });
});
