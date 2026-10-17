# Kara David's Wish Note — QAT Toolkit

## Repository Overview

This repository contains a small PHP and MariaDB web application and its quality-assurance test documentation. The application is intended for local development and classroom testing.

### Repository Contents

- `readme.md` — project overview and test case documentation.
- `webapp-generation-prompt.txt` — the AI prompt used to specify the test web application.
- `test-webapp.php` — the single-file PHP application with embedded HTML, CSS, and vanilla JavaScript.

The test-case source instructions also contain the filename reference `textcase.txt` (included verbatim as required by the assignment).

## Application Overview

The application is titled **Kara David's Wish Note**. It collects a target's first name, last name, government position, and optional location. Before submission, a confirmation modal displays the entered values. The PHP backend uses a MySQLi prepared statement to insert the first name, last name, and government position into the `main_corrupt_politicians` table in the `birthday_wishnote` database.

### Intentional Flaw Under Test

The supplied specification intentionally omits `Location` from the SQL `INSERT` column list and from `bind_param()`. Consequently, the location is displayed in the modal but is not saved by the INSERT statement. This is an intentional test condition, not a claim that the application stores all four values.

## Test Case Documentation

- **Test Case ID:** TC-DataEntry-001
- **Test Case Title:** Data Entry - Valid
- **Test Case Description:** Functional testing of the Data Entry page with backend validation using MySQL queries.
- **Preconditions:** The Kara David Birthday Wishnote application is installed locally; the MariaDB database is populated as needed for comparison; and the data-entry page is opened in a current Chrome or Firefox browser.
- **Test Data:** Five valid entries containing First Name, Last Name, Government Position, and Location, within the field length limits.

### Test Steps, Expected Results, and Actual Results

| Step | Action | Expected Result | Actual Result |
|---|---|---|---|
| a | Verify that the form labels appear in the correct order and that the text boxes are writable. | Labels appear in this order: “Target's First Name,” “Target's Last Name,” “Target's Government Position,” and “Target's Location.” All text boxes are writable. | Pending Execution |
| b | Verify the “Send Wish Note” button. | The button changes color on hover and is enabled when the database connection is available. | Pending Execution |
| c | Enter valid test data in the respective fields. | The fields accept valid entries within their maximum lengths: First Name 30, Last Name 30, Government Position 20, and Location 40 characters. | Pending Execution |
| d | Click “Send Wish Note.” | A confirmation modal titled “Verify Target Data” appears and displays the values entered in all four fields, including Location. | Pending Execution |
| e | Verify the values displayed in the modal against the entered test data. | The modal displays the same values that were entered for all four fields. | Pending Execution |
| f | Click “Cancel.” | The modal closes and the form is not submitted; no new record is inserted from this cancelled attempt. | Pending Execution |
| g | Click “Send Wish Note” again, then click “Confirm & Send” in the modal. | The confirmation modal closes as the form submits a POST request to the PHP backend. | Pending Execution |
| h | Verify the message displayed on the Data Entry page. | If the database INSERT succeeds, the page displays “Success! The wish note has been sent.” If it fails, an error message is displayed instead. | Pending Execution |
| i | Verify the test data in the database using a MySQL query. | The submitted First Name, Last Name, and Government Position should appear in the table if the INSERT succeeds. Location is intentionally not inserted by this query. | Pending Execution |

### Database Verification Query

Run the following commands in the local MySQL/MariaDB client after submitting a test entry:

```sql
USE birthday_wishnote;

SELECT *
FROM main_corrupt_politicians;
```

The query above selects and displays all rows and columns from the table. To inspect only the three fields inserted by the application, you may also run:

```sql
SELECT FirstName, LastName, GovPosition
FROM main_corrupt_politicians;
```

### Actual Results and Status

**Do not mark the test case Passed or Failed until the steps have actually been performed.** Replace each `Pending Execution` value with the observed result. If a step behaves differently from the expected result, record what happened and mark that step as failed.

- **Overall Status:** Pending Execution
- **Tester:** ____________________
- **Execution Date:** ____________________

## Local Setup and Execution

1. Start MariaDB/MySQL and Apache in the local development environment.
2. Confirm that the `birthday_wishnote` database and `main_corrupt_politicians` table exist and that the column names match the application query.
3. Place `test-webapp.php` in the web server's document root.
4. Open the application through the local web server, for example `http://localhost/test-webapp.php` if the file is in the document root.
5. Perform the test steps above using five valid test entries.
6. After each confirmed submission, run the database verification query and record the observed results.

> **Security note:** This exercise specifies a local root account with an empty password. Use this configuration only in an isolated local development environment. Do not expose it to a public network or use it in production.

## Limitations

- Location is collected and shown in the modal but intentionally omitted from database insertion.
- The success message confirms that the SQL statement executed successfully; it does not mean that Location was saved.
- Test results remain pending until the tester verifies them in the running application and database.
