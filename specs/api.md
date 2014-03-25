
Actions
=========

Users
    Read
    List
        fisrtName       fisrtname
        lastName        lastname
        branch          branch
        niveau          level
        filiere         speciality
        isStudent       is_student
        bdeMember       bde_member
    Image
    Badges
    Courses
    Organizations

Organizations
    Read
    List
    Image
    President

Endpoints
=============

/users                          List
/users/X                        Read (X is the login)
/users/X,Y,Z                    Read, multiple (X, Y, Z are logins)
/users/X/image                  Avatar for user X
/users/X/badges                 Badges for user X
/users/X/courses                Courses for user X
/users/X/organizations          Organizations for user X

/organizations                  List
/organizations/X                Read (X is the login)
/organizations/X,Y,Z            Read, multiple (X, Y, Z are logins)
/organizations/X/image          Avatar for organization X
/organizations/X/president      President for organization X