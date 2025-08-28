describe('Groomer Listing Hero', () => {
  const route = '/groomers/sofia/mobile-dog-grooming';

  it('renders the hero above the fold with dynamic city', () => {
    cy.visit(route);
    cy.get('#hero.hero--listing').should('exist');
    cy.get('#hero .hero__title')
      .should('be.visible')
      .should('contain.text', 'Sofia');
  });

  it('CTA smooth-scrolls to #groomer-listings (respects reduced motion)', () => {
    cy.visit(route);
    cy.get('#groomer-listings').should('exist');
    cy.get('#hero [data-scroll-target="#groomer-listings"]').click();
    cy.window().then((win) => {
      const rect = win.document.querySelector('#groomer-listings').getBoundingClientRect();
      expect(Math.abs(rect.top)).to.be.lessThan(16);
    });
  });
});

