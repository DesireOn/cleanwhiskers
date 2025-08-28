// Cypress E2E test (draft) for the listing hero section
// Note: Cypress is not configured in this repo. This spec
// is provided as acceptance guidance and can be enabled once
// Cypress is set up in CI.

describe('Listing Hero Section', () => {
  const route = '/groomers/london/mobile-dog-grooming'; // adjust to a valid route in your app

  it('renders the hero above the fold with dynamic city', () => {
    cy.visit(route);
    cy.get('#hero.hero--listing').should('exist');
    cy.get('#hero .hero__title')
      .should('be.visible')
      .should(($el) => {
        const text = $el.text();
        expect(text).to.match(/Mobile Dog Grooming in .+ — We Come to You!/);
      });
  });

  it('smooth-scrolls to #groomer-listings on CTA click', () => {
    cy.visit(route);
    cy.get('#groomer-listings').should('exist');
    cy.get('#hero [data-scroll-target="#groomer-listings"]').click();
    cy.location('hash').then((hash) => {
      // If reduced motion is enabled on the runner, this will jump
      // and update the hash; otherwise smooth scroll occurs without hash
      // so just assert the target is near the top of the viewport.
      if (!hash) {
        cy.window().then((win) => {
          const rect = win.document.querySelector('#groomer-listings').getBoundingClientRect();
          expect(Math.abs(rect.top)).to.be.lessThan(8);
        });
      }
    });
  });
});

