DROP TABLE IF EXISTS COMPANY;
CREATE TABLE COMPANY(
                              ID INT PRIMARY KEY     NOT NULL,
                              NAME           TEXT    NOT NULL,
                              AGE            INT     NOT NULL,
                              ADDRESS        CHAR(50),
                              SALARY         REAL
      );
DROP TABLE IF EXISTS DEPARTMENT;
CREATE TABLE DEPARTMENT(
                                     ID INT PRIMARY KEY      NOT NULL,
                                     DEPT           CHAR(50) NOT NULL,
                                     EMP_ID         INT      NOT NULL
      );
INSERT INTO DEPARTMENT (ID, DEPT, EMP_ID)
VALUES (1, 'IT Billing', 1 );

INSERT INTO DEPARTMENT (ID, DEPT, EMP_ID)
VALUES (2, 'Engineering', 2 );

INSERT INTO DEPARTMENT (ID, DEPT, EMP_ID)
VALUES (3, 'Finance', 7 );

INSERT INTO COMPANY (ID,NAME,AGE,ADDRESS,SALARY)
VALUES (1, 'Paul', 32, 'California', 20000.00 );

INSERT INTO COMPANY (ID,NAME,AGE,ADDRESS,SALARY)
VALUES (2, 'Allen', 25, 'Texas', 15000.00 );

INSERT INTO COMPANY (ID,NAME,AGE,ADDRESS,SALARY)
VALUES (3, 'Teddy', 23, 'Norway', 20000.00 );

INSERT INTO COMPANY (ID,NAME,AGE,ADDRESS,SALARY)
VALUES (4, 'Mark', 25, 'Rich-Mond ', 65000.00 );

SELECT EMP_ID, NAME, DEPT FROM COMPANY CROSS JOIN DEPARTMENT;
