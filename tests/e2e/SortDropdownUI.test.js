describe('Sort Dropdown UI', () => {
  it('displays options and updates URL on change', () => {
    const citySlug = 'sofia';
    const serviceSlug = 'mobile-dog-grooming';
    cy.visit(`/groomers/${citySlug}/${serviceSlug}`);

    cy.get('.sort-dropdown__select').should('be.visible').within(() => {
      cy.get('option').then(options => {
        const texts = [...options].map(o => o.text);
        expect(texts).to.deep.eq([
          'Recommended',
          'Price (low to high)',
          'Rating (high to low)'
        ]);
      });
    });

    cy.get('.sort-dropdown__select').select('Price (low to high)');
    cy.location('search').should('contain', 'sort=price');
  });
});
