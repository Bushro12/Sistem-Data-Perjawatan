# UAT List for Bahagian Resource (User Role = 2 - Admin)

## Authorization Tests
- [ ] Verify Admin (role=2) can access Bahagian list page
- [ ] Verify Admin (role=2) can view individual Bahagian record
- [ ] Verify Admin (role=2) can create new Bahagian
- [ ] Verify Admin (role=2) can edit existing Bahagian
- [ ] Verify Admin (role=2) CANNOT delete Bahagian record (should be denied per policy)
- [ ] Verify Admin (role=2) CANNOT restore deleted Bahagian
- [ ] Verify Admin (role=2) CANNOT permanently delete Bahagian
- [ ] Verify Admin (role=2) CANNOT bulk delete multiple Bahagians

## Create Form Tests
- [ ] Verify PTJ (ptj_id) select field is required and searchable with preload
- [ ] Verify Bahagian name (nama_bahagian) text input is required
- [ ] Verify Bahagian name auto-converts to uppercase on save (dehydrateStateUsing)
- [ ] Verify Bahagian name displays in uppercase in input field (extraInputAttributes)
- [ ] Verify form validation prevents save without PTJ selection
- [ ] Verify form validation prevents save without Bahagian name

## List Page Tests
- [ ] Verify table displays row number (Bil) column
- [ ] Verify table displays PTJ name (sortable, searchable)
- [ ] Verify table displays Bahagian name (sortable, searchable)
- [ ] Verify table displays Unit names with line breaks for multiple units
- [ ] Verify View action icon/button is available per record
- [ ] Verify Edit action icon/button is available per record
- [ ] Verify Delete action icon/button is available per record
- [ ] Verify Delete action confirmation modal shows heading: "Padam {record->nama_bahagian}"
- [ ] Verify Delete action confirmation modal shows description: correct warning text
- [ ] Verify Delete action has "Ya, Padam" submit button
- [ ] Verify Delete action has "Batal" cancel button
- [ ] Verify bulk delete checkbox is present in table rows

## Edit Form Tests
- [ ] Verify PTJ field is pre-populated with existing value
- [ ] Verify Bahagian name field is pre-populated with existing value
- [ ] Verify Bahagian name auto-converts to uppercase on update
- [ ] Verify form validation triggers on update with empty required fields

## Delete Tests (Expected to Fail for Admin role=2)
- [ ] Verify Delete button is visible but action returns 403/denied
- [ ] Verify appropriate error message shown when attempting delete
- [ ] Verify record remains unchanged after attempted delete

## Bulk Action Tests
- [ ] Verify bulk delete action button is visible in toolbar
- [ ] Verify bulk delete is denied for Admin role=2 (policy: only superadmin)
- [ ] Verify appropriate error message shown for bulk delete denial

## Data Integrity Tests
- [ ] Verify Bahagian name stored as UPPERCASE in database
- [ ] Verify PTJ foreign key (ptj_id) correctly stored
- [ ] Verify Unit relationship sub-records display correctly
- [ ] Verify search by PTJ name returns matching records
- [ ] Verify search by Bahagian name returns matching records
- [ ] Verify column sorting works for PTJ name column
- [ ] Verify column sorting works for Bahagian name column

## Navigation Tests
- [ ] Verify navigation icon displays as building-office icon
- [ ] Verify navigation label shows "Bahagian"
- [ ] Verify navigation group is "Kawalan"
- [ ] Verify navigation sort order is 22
- [ ] Verify record title attribute displays nama_bahagian value
