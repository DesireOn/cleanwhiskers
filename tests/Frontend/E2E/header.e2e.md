# Header Navigation E2E

## Desktop
1. Visit the home page on a viewport wider than 768px.
2. Verify the navigation menu displays "About", "Contact", "FAQ", "Blog", "Terms", "Privacy" horizontally.
3. Navigate to Blog and ensure the link has `aria-current="page"`.

## Mobile
1. Set viewport to 375px wide.
2. Activate the hamburger button.
3. Ensure body has `data-menu-open="true"` and scrolling is disabled.
4. Tab through links; focus cycles within the menu.
5. Press `Escape` to close the menu and verify body no longer has `data-menu-open`.
6. Click outside the menu and ensure it closes.
