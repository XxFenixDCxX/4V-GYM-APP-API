# 4vGYM API Documentation

This document outlines the specifications for the 4vGYM REST API. The API is designed to manage information related to activity types, monitors, and activities within a gym setting. The system involves creating controllers, persisting data in a relational model, establishing relationships between entities, and representing them in a relational model (M --> 1 and N --> M).

## Endpoints

### 1. Activity Types

#### `GET /activity-types`
Retrieve a list of activity types, including ID, name, and the number of monitors required.

### 2. Monitors

#### `GET /monitors`
Retrieve a list of monitors, including ID, name, email, phone, and photo.

#### `POST /monitors`
Create a new monitor and return the JSON with the information of the new monitor.

#### `PUT /monitors`
Edit existing monitors.

#### `DELETE /monitors`
Delete monitors.

### 3. Activities

#### `GET /activities`
Retrieve a list of activities, including information about types, included monitors, and date. It supports searching by date using a parameter in the format dd-MM-yyyy.

#### `POST /activities`
Create new activities and return information about the new activity. Ensure that the new activity has the required monitors according to the type of activity. The date and duration are not free-form fields. Only 90-minute classes starting at 09:00, 13:30, and 17:30 are allowed.

#### `PUT /activities`
Edit existing activities.

#### `DELETE /activities`
Delete activities.

## Database Schema

The database supporting the API should include the following tables:

1. **Monitors Table**
   - Columns: ID, Name, Email, Phone, Photo

2. **Activity Types Table**
   - Columns: ID, Name, Required Monitors

3. **Activities Table**
   - Columns: ID, Type (FK referencing Activity Types), Date, Duration

4. **Activities-Monitors Table (N-M Relationship)**
   - Columns: Activity ID (FK referencing Activities), Monitor ID (FK referencing Monitors)

All names of classes, tables, JSON modeling, etc., should be in English.

## Validation

The API should enforce validation for all POST requests to ensure data integrity.

## Notes

- Ensure that relationships between tables (foreign keys) are maintained.
- Use English language conventions for all aspects of the system, including class names, table names, and JSON representations.

Feel free to reach out if you have any questions or need further clarification. Happy coding!
