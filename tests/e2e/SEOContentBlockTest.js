describe('SEO Content Block', () => {
  const citySlug = 'sofia';
  const serviceSlug = 'mobile-dog-grooming';

  it('displays SEO content when available', () => {
    cy.visit(`/groomers/${citySlug}/${serviceSlug}`);
    cy.get('.seo-content').should('exist');
    cy.get('.seo-content__title').should('not.be.empty');
  });
});
