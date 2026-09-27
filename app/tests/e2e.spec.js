const { test, expect } = require('@playwright/test');
const AxeBuilder = require('@axe-core/playwright').default;

function watchErrors(page) {
  const errors=[];
  page.on('pageerror',error=>errors.push(error.message));
  page.on('console',message=>{if(message.type()==='error'&&!message.text().includes('fonts.googleapis'))errors.push(message.text())});
  return errors;
}

test('public navigation and core pages render without browser errors',async({page},testInfo)=>{
  const errors=watchErrors(page);
  await page.goto('/');
  await expect(page.getByRole('heading',{name:'A clearer way to request an ambulance.'})).toBeVisible();
  if(await page.getByRole('button',{name:'Toggle menu'}).isVisible())await page.getByRole('button',{name:'Toggle menu'}).click();
  await expect(page.getByRole('link',{name:'Track Request'})).toBeVisible();
  await page.getByRole('link',{name:'Services',exact:true}).click();
  await expect(page.getByRole('heading',{name:'Services',exact:true})).toBeVisible();
  if(await page.getByRole('button',{name:'Toggle menu'}).isVisible())await page.getByRole('button',{name:'Toggle menu'}).click();
  await page.getByRole('link',{name:'Search',exact:true}).click();
  await expect(page.getByRole('heading',{name:'Search ambulances'})).toBeVisible();
  await page.screenshot({path:`test-results/${testInfo.project.name}-search.png`,fullPage:true});
  expect(errors).toEqual([]);
});

test('public pages meet automated WCAG A and AA checks',async({page})=>{
  await page.goto('/#home');
  const results=await new AxeBuilder({page}).withTags(['wcag2a','wcag2aa','wcag21a','wcag21aa','wcag22aa']).analyze();
  expect(results.violations,JSON.stringify(results.violations,null,2)).toEqual([]);
});

test('keyboard users can skip directly to page content',async({page})=>{
  await page.goto('/#home');
  await page.keyboard.press('Tab');
  const skip=page.getByRole('link',{name:'Skip to main content'});
  await expect(skip).toBeFocused();
  await page.keyboard.press('Enter');
  await expect(page.locator('#page')).toBeFocused();
});

test('mobile navigation and layout fit the viewport',async({page},testInfo)=>{
  test.skip(testInfo.project.name!=='mobile','Mobile layout check');
  await page.goto('/');
  await page.getByRole('button',{name:'Toggle menu'}).click();
  await expect(page.getByRole('link',{name:'Track Request'})).toBeVisible();
  const overflow=await page.evaluate(()=>document.documentElement.scrollWidth-document.documentElement.clientWidth);
  expect(overflow).toBeLessThanOrEqual(1);
  await page.screenshot({path:`test-results/${testInfo.project.name}-home.png`,fullPage:true});
});

test('requester can register, submit, and open tracking',async({page})=>{
  const stamp=Date.now();
  await page.goto('/#account');
  await page.getByRole('button',{name:'Register'}).click();
  await page.getByLabel('Full name').fill('Browser Test User');
  await page.getByLabel('Email').fill(`browser-${stamp}@example.test`);
  await page.getByLabel('Phone').fill('01700000000');
  await page.getByLabel('Password (10+ characters)').fill('browser-test-password');
  await page.getByRole('button',{name:'Create account'}).click();
  await expect(page.getByText("Browser Test User's dashboard")).toBeVisible();
  await page.goto('/#request');
  await page.getByLabel('Pickup location').fill('Dhanmondi, Dhaka');
  await page.getByLabel('Destination').fill('Dhaka Medical College');
  await page.getByRole('button',{name:'Submit request'}).click();
  await expect(page.getByText('Awaiting dispatch').first()).toBeVisible();
  await page.goto('/#tracking');
  await expect(page.getByRole('heading',{name:'Track Request'})).toBeVisible();
  await expect(page.getByText('Dhanmondi, Dhaka').first()).toBeVisible();
});

test('requester can recover a forgotten password in local development',async({page},testInfo)=>{
  const stamp=Date.now(),email=`recovery-${testInfo.project.name}-${stamp}@example.test`;
  await page.goto('/#account');
  await page.getByRole('button',{name:'Register'}).click();
  await page.getByLabel('Full name').fill('Recovery Test User');
  await page.getByLabel('Email').fill(email);
  await page.getByLabel('Phone').fill('01700000002');
  await page.getByLabel('Password (10+ characters)').fill('original-password-123');
  await page.getByRole('button',{name:'Create account'}).click();
  await page.getByRole('button',{name:'Profile'}).click();
  await page.getByRole('button',{name:'Sign out'}).click();
  await expect(page.getByRole('heading',{name:'A clearer way to request an ambulance.'})).toBeVisible();
  await page.goto('/#account');
  await expect(page.getByRole('link',{name:'Forgot your password?'})).toBeAttached();
  await page.goto('/#forgot-password');
  await page.getByLabel('Email').fill(email);
  await page.getByRole('button',{name:'Create reset link'}).click();
  await page.getByRole('link',{name:'Open password reset'}).click();
  await page.getByLabel('New password',{exact:true}).fill('recovered-password-123');
  await page.getByLabel('Confirm new password').fill('recovered-password-123');
  await page.getByRole('button',{name:'Reset password'}).click();
  await page.getByLabel('Email').fill(email);
  await page.getByLabel('Password').fill('recovered-password-123');
  await page.locator('#login-form').getByRole('button',{name:'Sign in',exact:true}).click();
  await expect(page.getByText("Recovery Test User's dashboard")).toBeVisible();
});
